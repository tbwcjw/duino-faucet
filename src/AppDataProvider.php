<?php declare(strict_types=1);
namespace Faucet;

use Faucet\Config;
use Faucet\Session;
use Faucet\Captcha;

class AppDataProvider {
    public static function getData(?array $stats = null, ?string $status = null): array {
        return [
            'css' => Config::get(key: 'css'),
            'js' => Config::get(key: 'js'),
            'cooldown' => Config::get(key: 'cooldownDelta'),
            'csrf_token' => Session::getCSRF(),
            'synonym' => Config::getSynonym(),
            'captcha_img' => Captcha::getImage(),
            'captcha_hash' => Captcha::getHash(),
            'transactionAmount' => Config::get(key: 'transactionAmount'),
            'statistics' => $stats,
            'status' => $status
        ];
    }
}

?>