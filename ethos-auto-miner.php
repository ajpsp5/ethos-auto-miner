<?php

class MultiMiner {

    /**
     * Miner Settings
     *
     * @var array
     */
    protected $config = [];

    /**
     * Compatible Miners
     *
     * @var array
     */
    protected $miners = [];

    /**
     * Most Profitable Algorithm
     *
     * @var string
     */
    protected $mostProfitableAlgorithm;

    /**
     * MultiMiner constructor.
     *
     */
    public function __construct(){
        $this->log("Searching For Better Profits...");

        $basePath       = "/home/ethos/ethos-auto-miner";
        $this->config   = json_decode(`cat {$basePath}/conf.json`, true);
        $this->miners   = json_decode(`cat {$basePath}/algo-miners.json`, true);
    }

    /**
     * Search/Find/Switch Miner for Profit
     *
     */
    public function run(){
        $rig            = $this->config['rigname'];
        $algorithm      = $this->findMostProfitableAlgorithm();
        $algorithmPool  = $this->log("[{$algorithm}] is most profitable...")->findPoolFromAlgorithm($algorithm);
        $algorithmMiner = $this->findMinerFromAlgorithm($algorithm);

        if(!$this->checkIsAlgorithmMining($rig, $algorithmMiner, $algorithmPool)){
            $this->log('')->log('You like Ethos Autominer? Buy me a beer!');
            $this->log('BTC [ 16kcdsWD2h7YAdv9YGMyP9KCpcNEHE9zFM ]')->log('ETH [ 0xF1fEf7f9E5bD3386F04ae0De1546a8f16690F0c4 ]');

            $this->log('')->log('Found a bug? Want something added?');
            $this->log('Github: https://github.com/ajpsp5/ethos-auto-miner');
            return false;
        }

        $this->log("Algorithm: {$algorithm} | Miner: {$algorithmMiner} | Pool: {$algorithmPool}");
        $this->updateMinerAlgorithm($algorithm, $rig, $algorithmMiner, $algorithmPool)->restartMiner();
    }

    /**
     * Check is Algorithm Miner is Running
     *
     * @param $rig
     * @param $miner
     * @param $pool
     * @return bool
     */
    protected function checkIsAlgorithmMining($rig, $miner, $pool){
        $this->log("Checking current miner status...");

        if(!file_exists($this->config['statsfilepath'])){
            $this->log("Stats file does not exists in path: {$this->config['statsfilepath']}");
            return false;
        }

        $statsFile  = file_get_contents($this->config['statsfilepath']);

        if(!preg_match('/miner\:(.*)\s/m', $statsFile)){
            $this->log("You miner may not be running. Lets try to start it any way...");
            return true;
        }

        preg_match('/miner\:(.*)\s/m', $statsFile, $matches);

        if(($matches[1] == $miner) && (strpos($statsFile, $pool) !== false)){
            $this->log("Most profitable miner is running, exiting...");
            return false;
        }

        return true;
    }

    /**
     * Update Miner Algorithm
     *
     * @param $algorithm
     * @param $rig
     * @param $miner
     * @param $pool
     * @return $this
     */
    protected function updateMinerAlgorithm($algorithm, $rig, $miner, $pool){
        $this->log("Updating Miner Algorithm...");
        $localConfig = trim(preg_replace('/### <=Ethos-Auto-Miner-Settings=> ###\s[\S\s]*?### <=.*=> ###/m', '', file_get_contents($this->config['localconfigpath'])));

        $maxTemp    = null;
        $globalFan  = null;
        $wallet     = "wallet {$rig} {$this->config['proxywallet']}";
        $miner      = "miner {$rig} {$miner}";
        $attributes = implode("\n", array_map(function($key, $value) use($rig){
            return "{$key} {$rig} {$value}";
        }, array_keys($this->findMinerAttributesFromAlgorithm($algorithm)), $this->findMinerAttributesFromAlgorithm($algorithm)));

        if($flags = $this->findMinerFlagsFromAlgorithm($algorithm)){
            $flags  = "flg {$rig} {$flags}";
        }

        if(isset($this->config['default']['maxgputemp']) && ($maxTemp = $this->config['default']['maxgputemp'])){
            $maxTemp = "maxgputemp {$maxTemp}";
        }

        if(isset($this->config['default']['globalfan']) && ($globalFan = $this->config['default']['globalfan'])){
            $globalFan = "globalfan {$globalFan}";
        }

        $localConfig = "{$localConfig}
### <=Ethos-Auto-Miner-Settings=> ###
autoreboot {$this->config['default']['autoreboot']}
stratumproxy {$this->config['default']['stratumproxy']}
proxypool1 {$pool}
rigpool1 {$rig} {$pool}
{$maxTemp}
{$globalFan}
{$wallet}
{$miner}
{$flags}
{$attributes}
### <===========================> ###
        ";

        file_put_contents($this->config['localconfigpath'], $localConfig);
        $this->log($localConfig)->log("File [{$this->config['localconfigpath']}] updated...");

        return $this;
    }

    /**
     * Restart Miner
     *
     * @return $this
     */
    protected function restartMiner(){
        $this->log('Restarting Miner...')->log(`minestop && clear-thermals && minestart`);
        return $this;
    }

    /**
     * Find Most Profitable Algorithm
     *
     * @return string
     */
    protected function findMostProfitableAlgorithm(){
        $algorithms             = json_decode(file_get_contents($this->config['whattomineurl'] ?: ''), true) ?: [];
        $defaultAlgorithm       = $this->config['default']['algorithm'];
        $hasAlgorithmCandidate  = (isset($algorithms['coins']) && count($algorithms['coins'] ?: []));

        if(!$hasAlgorithmCandidate){
            return $defaultAlgorithm;
        }

        $mostProfitable = array_reduce($algorithms['coins'], function($carry, $coin){
            if(is_null($carry)){
                return $coin;
            }

            return ($coin['btc_revenue24'] > $carry['btc_revenue24']) ? $coin : $carry;
        });

        if(!isset($mostProfitable['algorithm'])){
            return $defaultAlgorithm;
        }

        return $this->mostProfitableAlgorithm = strtolower($mostProfitable['algorithm']);
    }

    /**
     * Find Pool For Algorithm
     *
     * @param $algorithm
     * @return mixed
     */
    protected function findPoolFromAlgorithm($algorithm){
        return isset($this->config['pools'][$algorithm]) ? $this->config['pools'][$algorithm] : $this->config['default']['pool'];
    }

    /**
     * Find Miner for Algorithm
     *
     * @param $algorithm
     * @return mixed
     */
    protected function findMinerFromAlgorithm($algorithm){
        return isset($this->miners[$algorithm]) ? $this->miners[$algorithm]['miner'] : $this->miners[$this->config['default']['algorithm']]['miner'];
    }

    /**
     * Find Miner Flags for Algorithm
     *
     * @param $algorithm
     * @return mixed
     */
    protected function findMinerFlagsFromAlgorithm($algorithm){
        return isset($this->miners[$algorithm]) ? $this->miners[$algorithm]['flags'] : $this->miners[$this->config['default']['algorithm']]['flags'];
    }

    /**
     * Find Miner Attributes for Algorithm
     *
     * @param $algorithm
     * @return array
     */
    protected function findMinerAttributesFromAlgorithm($algorithm){
        $defaultAttributes          = isset($this->config['default']['attributes']) ? $this->config['default']['attributes'] : [];
        $algorithmAttributes        = isset($this->miners[$algorithm]['attributes']) ? $this->miners[$algorithm]['attributes'] : [];

        $attributes = array_filter(array_merge($defaultAttributes, $algorithmAttributes));

        $this->log('Found Attributes:')->log(json_encode($attributes));

        return $attributes;
    }

    /**
     * Log Message
     *
     * @param $message
     * @return $this
     */
    protected function log($message){
        echo "{$message}\n";
        return $this;
    }
}

(new MultiMiner)->run();