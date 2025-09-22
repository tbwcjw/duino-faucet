<?php declare(strict_types=1);
namespace Faucet;

use Faucet\Config;
class Validation {
    public static function sanitizeInput(string $input): string {
        return htmlspecialchars(string: trim(string: $input), flags: ENT_QUOTES, encoding: 'UTF-8');
    }
    public static function validateWalletAddress(string $wallet_address): bool|int {
        if($wallet_address == null) return false;
	if(strlen(string: $wallet_address) < 1) return false;
        if($wallet_address == Config::get(key: 'faucet_username')) return false;
        return preg_match(pattern: '/^[a-zA-Z0-9_]{3,20}$/', subject: $wallet_address);
    }
    public static function sanitizeIPAdress(string $ip_address):?string {
        if($ip_address == null) return null;
        if(strlen(string: $ip_address) < 1) return null;
        return filter_var(value: $ip_address, filter: FILTER_VALIDATE_IP) ?: null;
    }
}
