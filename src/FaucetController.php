<?php declare(strict_types=1);
namespace Faucet;

use Faucet\Validation;
use Faucet\Database;
use Faucet\Config;

enum RewardStatus: string {
    case API_ERROR = "Couldn't communicate with the API server";
    case INVALID_CSRF = 'Invalid CSRF token.';
    case INVALID_CAPTCHA = 'Invalid captcha.';
    case INVALID_WALLET_ADDRESS = 'Invalid wallet address';
    case WALLET_NOT_FOUND = "Couldn't find wallet.";
    case FAUCET_BALANCE_LOW = "Transaction failed. Try again later.";
    case FAUCET_COOLDOWN = 'You have already used the faucet recently.';
    case TRANSACTION_INSERT_FAILED = 'Failed to record transaction, transaction cancelled.';
    case TRANSACTION_FAILED = 'Failed to send transaction, transaction cancelled.';
    case TRANSACTION_SUCCESS = 'Transaction successful.';
    case GENERAL_ERROR = 'General Error.';
}

class FaucetController {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }
    private static function checkCooldown(int $last): bool {
        $next_allowed = strtotime(datetime: Config::get(key: 'cooldownDelta'), baseTimestamp: (int)$last);
        if(time() >= $next_allowed) {
            return true;
        }
        return false;
    }

    public function tryReward(string $wallet_address, string $captcha_answer, string $captcha_hash, string $csrf_token): string {
        $wallet_address = Validation::sanitizeInput(input: $wallet_address);
        $captcha_answer = Validation::sanitizeInput(input: $captcha_answer);
        $captcha_hash = Validation::sanitizeInput(input: $captcha_hash);
        $csrf_token = Validation::sanitizeInput(input: $csrf_token);
        //Check API availability
        if(!API::checkAPIConnection()) {
            throw new \Exception(message: RewardStatus::API_ERROR->value);
        }
        //Validate the basics + Protect against replay attacks
        if ($_SESSION['csrf_token'] !== $csrf_token || $csrf_token != Session::getCSRF()) {
            Session::regenCSRF();
            throw new \Exception(message:  RewardStatus::INVALID_CSRF->value);
        }
        Session::regenCSRF();
        if(!Validation::validateWalletAddress(wallet_address: $wallet_address)) {
            throw new \Exception(message:  RewardStatus::INVALID_WALLET_ADDRESS->value);
        }
        //Validate Captcha
        if(!Captcha::validateCaptcha(client: $captcha_answer, server: $captcha_hash)) {
            throw new \Exception(message:  RewardStatus::INVALID_CAPTCHA->value);
        }
        //Check is user is on blacklist
        if($this->db->getBlacklisted(wallet_address: $wallet_address, ip_address: Session::getIP())) {
            throw new \Exception(message: RewardStatus::GENERAL_ERROR->value);
        }
        //Check if the faucet has been used by the user recently
        $result = $this->db->getLastFaucetUse(wallet_address: $wallet_address, ip_address: Session::getIP(), session_id: Session::getSessionID());
        if ($result !== null) {

            if(!$this->checkCooldown(last: $result['timestamp'])) {
                throw new \Exception(message: RewardStatus::FAUCET_COOLDOWN->value);
            }
        }
        //Check that the faucet balance has the requested amount
        if(API::getFaucetBalance() <= Config::get(key: 'minimum_faucet_balance', default: 100)) {
            throw new \Exception(message: RewardStatus::FAUCET_BALANCE_LOW->value);
        }
        //Ensure recipient wallet exists
        if (!API::checkWalletExists(recipient: $wallet_address)) {
            throw new \Exception(message: RewardStatus::WALLET_NOT_FOUND->value);
        }
        //Send the coin
        $result = API::sendCoin(recipient: $wallet_address);
        if($result === false) {
            throw new \Exception(message: RewardStatus::TRANSACTION_FAILED->value);
        }
        //Insert transaction record
        if (!$this->db->insertTransaction(wallet_address: $wallet_address, ip_address: Session::getIP(), session_id: Session::getSessionID(), transaction_amount: Config::get(key: 'transactionAmount'), transaction_id: $result, timestamp: time())) {
            throw new \Exception(message: RewardStatus::TRANSACTION_INSERT_FAILED->value);
        }
        return RewardStatus::TRANSACTION_SUCCESS->value;
        
    }
}
?>