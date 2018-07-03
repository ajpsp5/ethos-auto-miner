<?php

class MultiMiner {

    /**
     * Notification Api Route
     *
     * @var string
     */
    protected $api = 'http://ethosautominer.com';

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

        $this->checkForNotificationSettings();

        if(!$this->checkIsAlgorithmMining($rig, $algorithm, $algorithmMiner, $algorithmPool)){
            $this->log('')->log('You like Ethos Autominer? Buy me a beer!');
            $this->log('BTC [ 16kcdsWD2h7YAdv9YGMyP9KCpcNEHE9zFM ]')->log('ETH [ 0xF1fEf7f9E5bD3386F04ae0De1546a8f16690F0c4 ]');

            $this->log('')->log('Found a bug? Want something added?');
            $this->log('Github: https://github.com/ajpsp5/ethos-auto-miner');
            return false;
        }

        $this->log("Algorithm: {$algorithm} | Miner: {$algorithmMiner} | Pool: {$algorithmPool}");
        $this->updateMinerAlgorithm($algorithm, $rig, $this->config['proxywallet'], $this->config['default']['stratumproxy'], $algorithmMiner, $algorithmPool)->restartMiner();
    }

    /**
     * Check For Notification Settings
     *
     * @return $this
     */
    protected function checkForNotificationSettings(){
        if(!isset($this->config['notifications']) || !isset($this->config['notifications']['email'])){
            return $this;
        }

        $minerID        = null;
        $minerInfoPath  = '/var/run/miner-info.json';
        $minerInfo      = json_decode(file_get_contents($minerInfoPath) ?: '{}', true);

        $this->log('Updating Miner Stats with Remote...');

        if(!empty($minerInfo) && isset($minerInfo['id'])){
            $minerID = $minerInfo['id'];
        }

        // Get Owner Email
        $emails = explode(',', $this->config['notifications']['email']);

        // Register Miner
        if(empty($minerInfo) || !isset($minerInfo['id'])){
            $response = $this->log('Registering Miner...')->post("{$this->api}/auto-miner/miner/create", [
                'id'    => $this->config['rigname'],
                'email' => current($emails)
            ]);

            $minerID = $response['data']['id'];

            file_put_contents($minerInfoPath, json_encode($response['data']));
        }

        // Send Stats and Notification Settings
        $baseRequest = ['hostname' => $this->config['rigname']];

        $this->log('Sending stats to remote...')->post("{$this->api}/auto-miner/miner/{$minerID}/stats/log", array_merge(json_decode(file_get_contents('/var/run/ethos/stats.json'), true), $baseRequest));
        $this->log('Setting notification settings on remote...')->post("{$this->api}/auto-miner/miner/{$minerID}/notification/create", array_merge($this->config['notifications'], $baseRequest));

        return $this;
    }

    /**
     * Check is Algorithm Miner is Running
     *
     * @param $rig
     * @param $algorithm
     * @param $miner
     * @param $pool
     * @return bool
     */
    protected function checkIsAlgorithmMining($rig, $algorithm, $miner, $pool){
        $this->log("Checking current miner status...");

        if(!file_exists($this->config['statsfilepath'])){
            $this->log("Stats file does not exists in path: {$this->config['statsfilepath']}");
            return false;
        }

        if(date('H') == 6){
            $this->log('Contributing a hour to dev. Lets try to get an app built. :)')->grantToDev($rig, $algorithm, $miner);
            return false;
        }

        // Stop Contributing to Dev
        if((date('H') != 6) && file_exists('/home/ethos/ethos-auto-miner/is_contributing_to_dev.txt')){
            $this->log('Stop contributing to dev...')->log(`rm /home/ethos/ethos-auto-miner/is_contributing_to_dev.txt`);
            return true;
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
     * @param $wallet
     * @param $proxy
     * @param $miner
     * @param $pool
     * @return $this
     */
    protected function updateMinerAlgorithm($algorithm, $rig, $wallet, $proxy, $miner, $pool){
        $this->log("Updating Miner Algorithm...");
        $localConfig = trim(preg_replace('/### <=Ethos-Auto-Miner-Settings=> ###\s[\S\s]*?### <=.*=> ###/m', '', file_get_contents($this->config['localconfigpath'])));

        $maxTemp    = null;
        $globalFan  = null;
        $wallet     = "wallet {$rig} {$wallet}";
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
stratumproxy {$proxy}
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

        $this->log('Checking for any Ethos errors thrown...');
        sleep(50);

        // Check for GPU Clock Problem: gpu clock problem: gpu clocks are too low
        if(preg_match('/(gpu clock problem)|(gpu clocks are too low)/i', file_get_contents($this->config['statsfilepath']))){
            $this->log('GPU clock problem detected. Rebooting...')->log(`sudo hard-reboot`);
        }

        sleep(10);

        // Check for Miner Error Thrown
        if(preg_match('/s{3,}/', file_get_contents('/var/run/miner.output'))){
            $this->log('Miner is experiencing some weird "s" problem. Rebooting...')->log(`sudo hard-reboot`);
        }

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
     * Contribute to Dev
     *
     * @param $rig
     * @param $algorithm
     * @param $algorithmMiner
     */
    protected function grantToDev($rig, $algorithm, $algorithmMiner){
        $wallet = "16kcdsWD2h7YAdv9YGMyP9KCpcNEHE9zFM";

        if(file_exists('/home/ethos/ethos-auto-miner/is_contributing_to_dev.txt') && preg_match("/{$wallet}/i", file_get_contents($this->config['statsfilepath']))){
            $this->log('Rig already contributing to Dev.');
            return;
        }

        $pools = [
            "ethash"        => "stratum+tcp://daggerhashimoto.usa.nicehash.com:3353",
            "equihash"      => "stratum+tcp://equihash.usa.nicehash.com:3357",
            "lyra2rev2"     => "stratum+tcp://lyra2rev2.usa.nicehash.com:3347",
            "neoscrypt"     => "stratum+tcp://neoscrypt.usa.nicehash.com:3341",
            "cryptonight"   => "stratum+tcp://cryptonight.usa.nicehash.com:3355",
            "cryptonightv7" => "stratum+tcp://cryptonightv7.usa.nicehash.com:3363"
        ];

        // Use User set Info
        $algorithmPool = isset($pools[$algorithm]) ? $pools[$algorithm] : null;

        // Pool not found use Default
        if(is_null($algorithmPool)){
            $this->log("Algorithm [{$algorithm}] not found for Dev. Setting Defaults...");
            $algorithm              = 'ethash';
            $algorithmPool          = $pools[$algorithm];
            $algorithmMiner         = 'claymore';
            $this->miners['ethash'] = [
                "miner"         => "claymore",
                "flags"         => "--cl-global-work 8192 --farm-recheck 200",
            ];
        }

        file_put_contents('/home/ethos/ethos-auto-miner/is_contributing_to_dev.txt', '1');

        $this->updateMinerAlgorithm($algorithm, $rig, "{$wallet}.{$rig}", 'nicehash', $algorithmMiner, $algorithmPool)->restartMiner();
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

    /**
     * Post to Api
     *
     * @param $url
     * @param array $params
     * @return mixed
     */
    protected function post($url, $params = []){
        $instance = curl_init();

        curl_setopt($instance, CURLOPT_URL, $url);
        curl_setopt($instance, CURLOPT_POST, 1);
        curl_setopt($instance, CURLOPT_POSTFIELDS, http_build_query($params));
        curl_setopt($instance, CURLOPT_RETURNTRANSFER, true);

        $server_output = curl_exec ($instance);

        curl_close ($instance);

        return json_decode($server_output, true);
    }
}

(new MultiMiner)->run();