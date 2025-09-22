<?php declare(strict_types=1);
namespace Faucet;

use Faucet\Database;
use Faucet\Config;
use Faucet\API;
class Statistics {
    private static Database $db;
    private static bool $enabled;
    private static string $delta;

    public static function init(): void {
        self::$db = Database::getInstance();
        self::$enabled = (bool)Config::get(key: 'stats_cache_enabled', default: true);
        self::$delta = (string)Config::get(key: 'stats_cache_invalidate_delta', default: "+1 hours");
    }
    public static function getCachedStatistics(): array {
        $cached = self::$db->getStatistics();           //get from db
        $expiration = strtotime(datetime: self::$delta, baseTimestamp: $cached['timestamp']); 

        if(self::$enabled) {                            //if cache enabled
            if(time() >= $expiration) {                 //if cache expired
                $_24hour = self::$db->count24Hours();   //count statistics
                $_alltime = self::$db->countAll();      //count statistics
                $balance = API::getFaucetBalance();     //get balance from api \/ update statistic db
                self::$db->updateStatistics(_24_hour: $_24hour, _alltime: $_alltime, balance: $balance);
                $cached['human_ts'] = date(format: 'Y-m-d H:i:s', timestamp: time());
                $cached['donors'] = self::$db->getDonors();
                error_log(message: "Cache refeshed. Faucet balance {$balance} duco. 24 hour count: {$_24hour['row_count']} ({$_24hour['total_amount']} duco). All Time: {$_alltime['row_count']} ({$_alltime['total_amount']} duco)");
                return $cached;
            } else {
                //use cached. cache isn't expired yet
                $cached['human_ts'] = date(format: 'Y-m-d H:i:s', timestamp: $cached['timestamp']);
                $cached['donors'] = self::$db->getDonors();
                return $cached;
            }
        }
        //cache is disabled, always update.
        $_24hour = self::$db->count24Hours();   //count statistics
        $_alltime = self::$db->countAll();      //count statistics
        $balance = API::getFaucetBalance();     //get balance from api \/ update statistic db
        self::$db->updateStatistics(_24_hour: $_24hour, _alltime: $_alltime, balance: $balance);
        
        $cached['human_ts'] = date(format: 'Y-m-d H:i:s', timestamp: time());
        $cached['donors'] = self::$db->getDonors();
        return $cached;
    }

}