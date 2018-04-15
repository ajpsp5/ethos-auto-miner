# ethos-auto-miner
Automatically switch miner to the most profitable algorithm for Ethos.

Setup
----
Clone the repository to your ethos home directory `/home/ethos`.
```bash
$ git clone https://github.com/ajpsp5/ethos-auto-miner
```

Navigate into the `/home/ethos/ethos-auto-miner` directory and run the installer
```bash
$ cd /home/ethos/ethos-auto-miner && php install.php
```

**The installer will clear any remote or local config files so make sure you save a copy of your configs.**

After the install script finishes, set the cronjob. Run command:
```bash
$ crontab -e
```

Cron Schedule:
```
*/50 * * * * /usr/bin/php /home/ethos/ethos-auto-miner/ethos-auto-miner.php > /home/ethos/ethos-auto-miner/output.log 2>&1
```

It is possible that ethos may throw the common error `gpu clocks too low` or `gpu clock problem`. If this happen, perform a hard reboot of your rig.

Default Configs and Settings
----
There are two default settings added for either AMD or Nivida rigs. If you have an AMD rig, you simple start with the AMD defaults
```bash
$ mv /home/ethos/ethos-auto-miner/_amd_algo-miners.json algo-miners.json && mv /home/ethos/ethos-auto-miner/_amd_config.json config.json
```

Config Usage
---
This is where you will set your **wallet address** and your **rig name** from ethos. Be sure to set your [what-to-mine](http://whattomine.com/coins) json address.

To generate the correct what-to-mine address, select GPU types, click calculate, then add `.json` to the url. Here's an example of a rig with 6 RX580 GPUs: 
```
http://whattomine.com/coins.json?utf8=%E2%9C%93&adapt_q_280x=0&adapt_q_380=0&adapt_q_fury=0&adapt_q_470=0&adapt_q_480=0&adapt_q_570=0&adapt_q_580=6&adapt_580=true&adapt_q_vega56=0&adapt_q_vega64=0&adapt_q_750Ti=0&adapt_q_1050Ti=0&adapt_q_10606=0&adapt_q_1070=0&adapt_q_1070Ti=0&adapt_q_1080=0&adapt_q_1080Ti=0&eth=true&factor%5Beth_hr%5D=181.2&factor%5Beth_p%5D=810.0&grof=true&factor%5Bgro_hr%5D=111.0&factor%5Bgro_p%5D=690.0&x11gf=true&factor%5Bx11g_hr%5D=41.4&factor%5Bx11g_p%5D=660.0&cn=true&factor%5Bcn_hr%5D=4140.0&factor%5Bcn_p%5D=690.0&cn7=true&factor%5Bcn7_hr%5D=4140.0&factor%5Bcn7_p%5D=690.0&eq=true&factor%5Beq_hr%5D=1740.0&factor%5Beq_p%5D=720.0&lre=true&factor%5Blrev2_hr%5D=34200.0&factor%5Blrev2_p%5D=720.0&ns=true&factor%5Bns_hr%5D=4920.0&factor%5Bns_p%5D=900.0&bk14=true&factor%5Bbk14_hr%5D=8100.0&factor%5Bbk14_p%5D=780.0&pas=true&factor%5Bpas_hr%5D=4140.0&factor%5Bpas_p%5D=870.0&skh=true&factor%5Bskh_hr%5D=111.0&factor%5Bskh_p%5D=690.0&n5=true&factor%5Bn5_hr%5D=120.0&factor%5Bn5_p%5D=690.0&factor%5Bl2z_hr%5D=420.0&factor%5Bl2z_p%5D=300.0&xn=true&factor%5Bxn_hr%5D=9.6&factor%5Bxn_p%5D=720.0&factor%5Bcost%5D=0.12&sort=Profitability24&volume=0&revenue=24h&factor%5Bexchanges%5D%5B%5D=&factor%5Bexchanges%5D%5B%5D=abucoins&factor%5Bexchanges%5D%5B%5D=bitfinex&factor%5Bexchanges%5D%5B%5D=bittrex&factor%5Bexchanges%5D%5B%5D=binance&factor%5Bexchanges%5D%5B%5D=cryptopia&factor%5Bexchanges%5D%5B%5D=hitbtc&factor%5Bexchanges%5D%5B%5D=poloniex&factor%5Bexchanges%5D%5B%5D=yobit&dataset=Main&commit=Calculate
```

Config Example: `/home/ethos/ethos-auto-miner/config.json`
```json
{
    "rigname"           : "0edcb9",
    "proxywallet"       : "16kcdsWD2h7YAdv9YGMyP9KCpcNEHE9zFM.Rig2",
    "whattomineurl"     : "http://whattomine.com/coins.json?utf8=%E2%9C%93&adapt_q_280x=0&adapt_q_380=0&adapt_q_fury=0&adapt_q_470=0&adapt_q_480=0&adapt_q_570=0&adapt_q_580=0&adapt_q_vega56=0&adapt_q_vega64=0&adapt_q_750Ti=0&adapt_q_1050Ti=0&adapt_q_10606=0&adapt_q_1070=4&adapt_1070=true&adapt_q_1070Ti=0&adapt_q_1080=1&adapt_1080=true&adapt_q_1080Ti=0&eth=true&factor%5Beth_hr%5D=143.3&factor%5Beth_p%5D=620.0&grof=true&factor%5Bgro_hr%5D=178.5&factor%5Bgro_p%5D=670.0&x11gf=true&factor%5Bx11g_hr%5D=59.5&factor%5Bx11g_p%5D=625.0&cn=true&factor%5Bcn_hr%5D=3100.0&factor%5Bcn_p%5D=500.0&cn7=true&factor%5Bcn7_hr%5D=3100.0&factor%5Bcn7_p%5D=500.0&eq=true&factor%5Beq_hr%5D=2270.0&factor%5Beq_p%5D=610.0&lre=true&factor%5Blrev2_hr%5D=188500.0&factor%5Blrev2_p%5D=670.0&ns=true&factor%5Bns_hr%5D=5060.0&factor%5Bns_p%5D=670.0&bk14=true&factor%5Bbk14_hr%5D=12900.0&factor%5Bbk14_p%5D=650.0&pas=true&factor%5Bpas_hr%5D=5050.0&factor%5Bpas_p%5D=630.0&skh=true&factor%5Bskh_hr%5D=146.5&factor%5Bskh_p%5D=630.0&n5=true&factor%5Bn5_hr%5D=234.0&factor%5Bn5_p%5D=670.0&factor%5Bl2z_hr%5D=420.0&factor%5Bl2z_p%5D=300.0&xn=true&factor%5Bxn_hr%5D=15.8&factor%5Bxn_p%5D=610.0&factor%5Bcost%5D=0.12&sort=Profitability24&volume=0&revenue=24h&factor%5Bexchanges%5D%5B%5D=&factor%5Bexchanges%5D%5B%5D=abucoins&factor%5Bexchanges%5D%5B%5D=bitfinex&factor%5Bexchanges%5D%5B%5D=bittrex&factor%5Bexchanges%5D%5B%5D=binance&factor%5Bexchanges%5D%5B%5D=cryptopia&factor%5Bexchanges%5D%5B%5D=hitbtc&factor%5Bexchanges%5D%5B%5D=poloniex&factor%5Bexchanges%5D%5B%5D=yobit&dataset=&commit=Calculate",
    "statsfilepath"     : "/var/run/ethos/stats.file",
    "localconfigpath"   : "/home/ethos/local.conf",
    "default"       : { // Default Algorithm and Settings
        "algorithm"     : "equihash",
        "flags"         : "",
        "pool"          : "stratum+tcp://equihash.usa.nicehash.com:3357",
        "autoreboot"    : "10",
        "stratumproxy"  : "nicehash",
        "attributes"    : {
            "cor"   : "+125 +125 +125 +125 +85",
            "mem"   : "+275 +275 +275 +275 +375",
            "pwr"   : "130 120 120 130 170",
            "fan"   : "50 50 50 50 75"
        }
    },
    "pools"         : { // Pools for each Algorithm Type
        "ethash"        : "stratum+tcp://daggerhashimoto.usa.nicehash.com:3353",
        "equihash"      : "stratum+tcp://equihash.usa.nicehash.com:3357",
        "lyra2rev2"     : "stratum+tcp://lyra2rev2.usa.nicehash.com:3347",
        "neoscrypt"     : "stratum+tcp://neoscrypt.usa.nicehash.com:3341",
        "cryptonight"   : "stratum+tcp://cryptonight.usa.nicehash.com:3355",
        "cryptonightv7" : "stratum+tcp://cryptonightv7.usa.nicehash.com:3363"
    }
}
```

Algorithm Settings
----
This where you can overclock settings based on the Algorithm type.

File Example: `/home/ethos/ethos-auto-miner/algo-miners.json`
```json
{
    "cryptonightv7" : {
        "miner"         : "claymore-xmr",
        "flags"         : "-pow7 1",
        "attributes"    : {

        }
    },
    "cryptonight" : {
        "miner"         : "claymore-xmr",
        "flags"         : "-pow7 1"
    },
    "ethash"        : {
        "miner"         : "claymore",
        "flags"         : "--cl-global-work 8192 --farm-recheck 200",
        "attributes"    : { // Attributes can use any rig specific Ethos Config setting
            "cor"   : "2000 2000 1200 1200 1200 1200",
            "mem"   : "2200 2250 2250 2000 2250 2000",
            "pwr"   : "5 5 5 5 5 5"
        }
    },
    "equihash"      : {
        "miner" : "ewbf-zcash",
        "flags" : ""
    },
    "lyra2rev2"     : {
        "miner" : "ccminer",
        "flags" : "-a lyra2v2"
    },
    "neoscrypt"     : {
        "miner" : "ccminer",
        "flags" : "-a neoscrypt"
    }
}
```

Support
----
If you should find any bugs or need any enhancements please open a Github issue. I will try to get to it as soon as I can. Happy Mining Guys!

Donations
----
Your rig will donate one hour a day of mining time, roughly $0.15/day, towards improving the program and towards a free mobile project under way for monintoring your Ethos rig. If you have any questions, feedback or suggestions please submit a Github issue.

**Buy me a Beer** 😊

BTC: `16kcdsWD2h7YAdv9YGMyP9KCpcNEHE9zFM`

ETH: `0xF1fEf7f9E5bD3386F04ae0De1546a8f16690F0c4`
