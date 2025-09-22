<?php declare(strict_types=1);
namespace Faucet;

use Faucet\Config;
class API {
    private static string $apiUrl;
    private static string $faucet_username;
    private static string $faucet_password;
    private static float $transaction_amount;
    private static string $memo;
    public static function init(): void {
        self::$apiUrl = Config::get(key: 'api_server');
        self::$faucet_username = Config::get(key: 'faucet_username');
        self::$faucet_password = Config::get(key: 'faucet_password');
        self::$transaction_amount = Config::get(key: 'transactionAmount');
        self::$memo = Config::get(key: 'transactionMessage');
    }
    private static function curlExec(string $url):\CurlHandle|string|false {
        $curl = curl_init(url: $url);
        curl_setopt(handle: $curl, option: CURLOPT_RETURNTRANSFER, value: true);
        curl_setopt(handle: $curl, option: CURLOPT_SSL_VERIFYPEER, value: true);
        curl_setopt(handle: $curl, option: CURLOPT_SSL_VERIFYHOST, value: 2);
        curl_setopt(handle: $curl, option: CURLOPT_HTTPHEADER, value: [
            'Content-Type: application/json',
        ]);
        $response = curl_exec(handle: $curl);

        if (curl_errno(handle: $curl)) return false;
        curl_close(handle: $curl);
        
        return $response;
    }
    public static function checkAPIConnection():bool {
        $curl = self::curlExec(url: self::$apiUrl);
        if($curl !== false) return true;
        return false;
    }

    public static function getFaucetBalance(): float {
        $url = self::$apiUrl . '/balances/' . self::$faucet_username;
        $curl = self::curlExec(url: $url);

        if(!$curl) return 0;

        $result = json_decode(json: $curl, associative: true);
        
        if (!$result || !isset($result['result']['balance'])) {
            return 0;
        }
        return $result['result']['balance'] ?? 0;
    }
    public static function checkWalletExists(string $recipient): bool {
        $url = self::$apiUrl . '/balances/' . $recipient;
        $curl = self::curlExec(url: $url);

        if(!$curl) return false;

        $result = json_decode(json: $curl, associative: true);
        if(isset($result['success']) && $result['success'] === true) {
            return true;
        }
        return false;
    }
    public static function sendCoin(string $recipient): bool|string {
        $params = [
            'username'  => self::$faucet_username,
            'password'  => self::$faucet_password,
            'recipient' => $recipient,
            'amount'    => self::$transaction_amount,
            'memo'      => self::$memo,
        ];
        $url = self::$apiUrl . 'transaction/?' . http_build_query(data: $params);
        $curl = self::curlExec(url: $url);

        if(!$curl) return false;
        
        $result = json_decode(json: $curl, associative: true);

        if(isset($result['success']) && $result['success'] === true) {
            $parts = explode(separator: ',', string: $result['result']);
            $hash = end(array:$parts);
            return $hash ?? "tid_not_found";
        }
        return false;
    }
}
