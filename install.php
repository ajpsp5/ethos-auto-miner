<?php
/**
 * Created by PhpStorm.
 * User: adamgriffin
 * Date: 4/14/18
 * Time: 2:07 PM
 */

class Installer {

    /**
     * Miner Settings
     *
     * @var array
     */
    protected $config = [];

    /**
     * Autominer Base Path
     *
     * @var string
     */
    protected $basePath = '/home/ethos/ethos-auto-miner';

    /**
     * Installer constructor.
     *
     */
    public function __construct(){
        $this->log("Starting installer...");

        $this->config   = json_decode(`cat {$this->basePath}/conf.json`, true);
    }

    /**
     * Run the Installer
     *
     */
    public function run(){
        $this->clearConfigs();
        $this->clearStatsFile();
        $this->startEthosAutoMiner();

        $this->log('Ethos Autominer has been commanded to start.');
        $this->log('Be sure to set the cronjob for keeping the miner updated.');
        $this->log('')->log('command: [ $ crontab -e ]', 'yellow');
        $this->log('Set job:')->log("*/50 * * * * /usr/bin/php {$this->basePath}/ethos-auto-miner.php > {$this->basePath}/output.log 2>&1", 'yellow');

        $this->log('')->log('You like Ethos Autominer? Buy me a beer!');
        $this->log('BTC [ 16kcdsWD2h7YAdv9YGMyP9KCpcNEHE9zFM ]', 'green')->log('ETH [ 0xF1fEf7f9E5bD3386F04ae0De1546a8f16690F0c4 ]', 'green');

        $this->log('')->log('Found a bug? Want something added?');
        $this->log('Github: https://github.com/ajpsp5/ethos-auto-miner');
    }

    /**
     * Clear Configs
     *
     */
    protected function clearConfigs(){
        $this->log('Clearing Remote Config [Prevents your remote from overriding the Algorithm Found]...');
        file_put_contents('/home/ethos/remote.conf', '');

        $this->log('Clearing Local Config...');
        file_put_contents('/home/ethos/remote.conf', '');
    }

    /**
     * Clear Stats File
     *
     */
    protected function clearStatsFile(){
        $this->log('Clear stats file...');
        file_put_contents($this->config['statsfilepath'], '');
    }

    /**
     * Start Ethos Autominer
     *
     */
    protected function startEthosAutoMiner(){
        $this->log('Starting Ethos Autominer...')->log(`chmod +x {$this->basePath}/ethos-auto-miner.php && php {$this->basePath}/ethos-auto-miner.php`, 'white');
    }

    /**
     * Log Message
     *
     * @param $message
     * @param string $color
     * @return $this
     */
    protected function log($message, $color = 'blue'){
        $outputColors = [
            'green'     => '0;32',
            'blue'      => '0;34',
            'white'     => '1;37',
            'yellow'    => '1;33'
        ];

        echo "\033[{$outputColors[$color]}m{$message}\033[0m\n";
        return $this;
    }
}

(new Installer)->run();