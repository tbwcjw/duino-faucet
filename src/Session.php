<?php declare(strict_types=1);
namespace Faucet;

use Faucet\Captcha;
use Faucet\Validation;

class Session {
    public static string|null $ip_address;
    public static string|false $session_id;
    public static string $csrf_token;
    public function __construct() {
        self::$ip_address = self::setIP();
        self::$session_id = session_id();
        self::$csrf_token = self::setCSRF();

        Captcha::init();
        self::setSession();
    }


    public function setSession(): void {
        $_SESSION['csrf_token'] = self::$csrf_token;
        $_SESSION['ip_address'] = self::$ip_address;
        $_SESSION['session_id'] = self::$session_id;
    }

    private function setIP(): string|null {
        $ip = null;
        if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
	    $ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
	} elseif (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (!empty($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        } else {
	    $ip = "0.0.0.0";
	}
        return Validation::sanitizeIPAdress(ip_address: $ip);
    }

    public static function regenCSRF(): void {
        $_SESSION['csrf_token'] = bin2hex(string: random_bytes(length: 32)); 
        self::$csrf_token = $_SESSION['csrf_token'];
    }
    public function setCSRF(): string {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(string: random_bytes(length: 32)); 
        }
        return $_SESSION['csrf_token'];
    }

    public static function getSessionID(): bool|string {
        return self::$session_id;
    }

    public static function getIP(): string|null {
        return self::$ip_address;
    }

    public static function getCSRF(): string {
        return self::$csrf_token;
    }
}

?>
