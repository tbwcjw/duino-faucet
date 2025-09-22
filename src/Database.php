<?php declare(strict_types=1);
namespace Faucet;
class Database {
    private static $instance = null;
    private $db;

    private function __construct() {
        $this->db = new \SQLite3(filename:__DIR__ . Config::get(key: 'database_path'));
        $this->db->exec(query: "PRAGMA journal_mode = WAL;");
        $this->db->exec(query: "CREATE TABLE IF NOT EXISTS transactions (
        id INTEGER PRIMARY KEY AUTOINCREMENT, 
        wallet_address TEXT NOT NULL, 
        ip_address TEXT, 
        session_id TEXT, 
        transaction_amount NUMERIC NOT NULL, 
        transaction_id TEXT NOT NULL, 
        timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY(id AUTOINCREMENT));");

        $this->db->exec(query: "CREATE TABLE IF NOT EXISTS statistics (
        transactions_24hour_count INTEGER NOT NULL DEFAULT 0,
        transactions_24hour_amount NUMERIC NOT NULL DEFAULT 0,
        transactions_alltime_count INTEGER NOT NULL DEFAULT 0,
        transactions_alltime_amount NUMERIC NOT NULL DEFAULT 0,
        faucet_balance NUMERIC NOT NULL DEFAULT 0,
        timestamp DATETIME DEFAULT CURRENT_TIMESTAMP)");

        $this->db->exec(query: "CREATE TABLE IF NOT EXISTS donors (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        wallet_address TEXT NOT NULL UNIQUE,
        PRIMARY KEY(id AUTOINCREMENT))");

        $this->db->exec(query: "CREATE TABLE IF NOT EXISTS blacklist (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        wallet_address TEXT UNIQUE,
        ip_address TEXT,
        reason TEXT,
        PRIMARY KEY(id AUTOINCREMENT))");
    }

    public function __destruct() {
        if ($this->db) {
            $this->db->close();
        }
    }

    public static function getInstance(): Database {
        if(self::$instance === null) {
            self::$instance = new Database();
            
        }
        return self::$instance;
    }
    public function getDb(): \SQLite3 {
        return $this->db;
    }
    public function insertTransaction(string $wallet_address, string $ip_address, string $session_id, float $transaction_amount, string $transaction_id, int $timestamp): bool {
        
        $stmt = $this->db->prepare(query: 'INSERT into transactions (wallet_address, ip_address, session_id, transaction_amount, transaction_id, timestamp) VALUES (:wallet_address, :ip_address, :session_id, :transaction_amount, :transaction_id, :timestamp)');
        if ($stmt === false) {
            throw new \Exception(message: $this->db->lastErrorMsg());
        }
        $stmt->bindValue(param: ':wallet_address', value: $wallet_address);
        $stmt->bindValue(param: ':ip_address', value: $ip_address);
        $stmt->bindValue(param: ':session_id', value: $session_id);
        $stmt->bindValue(param: ':transaction_amount', value: $transaction_amount);
        $stmt->bindValue(param: ':transaction_id', value: $transaction_id);
        $stmt->bindValue(param: ':timestamp', value: $timestamp);
        if($stmt->execute()) {
            return true;
        }
        return false;
    }
    public function getLastFaucetUse(string $wallet_address, string $ip_address, string $session_id): array|null {
        $stmt = $this->db->prepare(query: 'SELECT timestamp FROM transactions WHERE wallet_address = :wallet_address OR ip_address = :ip_address OR session_id = :session_id ORDER BY timestamp DESC LIMIT 1');
        if ($stmt === false) {
            throw new \Exception(message: $this->db->lastErrorMsg());
        }
        $stmt->bindValue(param: ':wallet_address', value: $wallet_address);
        $stmt->bindValue(param: ':ip_address', value: $ip_address);
        $stmt->bindValue(param: ':session_id', value: $session_id);
        $result = $stmt->execute()->fetchArray(mode: SQLITE3_ASSOC);
        if($result === false) return null;
        return $result;
    }

    public function getStatistics(): array|bool {
        $stmt = $this->db->prepare(query: 'SELECT * FROM statistics');
        if ($stmt === false) {
            throw new \Exception(message: $this->db->lastErrorMsg());
        }
        $result = $stmt->execute()->fetchArray(mode: SQLITE3_ASSOC);
        return $result;
    }
    public function getDonors(): string|null {
        $result = $this->db->querySingle(query: 'SELECT GROUP_CONCAT(wallet_address, \', \') AS wallet_addresses FROM donors;', entireRow: true);
        if ($result === false) {
            throw new \Exception(message: $this->db->lastErrorMsg());
        }
        return $result['wallet_addresses'];
    }
    public function count24Hours(): array {
        $now = time();
        $minus24Hours = $now - (24*60*60);

        $stmt = $this->db->prepare(query: 'SELECT COUNT(*) AS row_count, SUM(transaction_amount) AS total_amount FROM transactions WHERE timestamp BETWEEN :minus24Hours AND :now');
        if ($stmt === false) {
            throw new \Exception(message: $this->db->lastErrorMsg());
        }
        $stmt->bindValue(param: ':minus24Hours', value: $minus24Hours, type: SQLITE3_INTEGER);
        $stmt->bindValue(param: ':now', value: $now, type: SQLITE3_INTEGER);
        
        $result = $stmt->execute()->fetchArray(mode: SQLITE3_ASSOC);

        return $result;
    }
    public function countAll(): array {
        $stmt = $this->db->prepare(query: 'SELECT COUNT(*) AS row_count, SUM(transaction_amount) AS total_amount FROM transactions');
        if ($stmt === false) {
            throw new \Exception(message: $this->db->lastErrorMsg());
        }
        $result = $stmt->execute()->fetchArray(mode: SQLITE3_ASSOC);
        return $result;
    }
    public function updateStatistics($_24_hour, $_alltime, $balance): bool {
        $now = time();
        $stmt = $this->db->prepare(query: 'UPDATE statistics SET transactions_24hour_count = COALESCE(:_24hour_count, 0), transactions_24hour_amount = COALESCE(:_24hour_amount, 0), transactions_alltime_count = COALESCE(:_alltime_count, 0), transactions_alltime_amount = COALESCE(:_alltime_amount, 0),faucet_balance = COALESCE(:faucet_balance, 0), timestamp = :timestamp;');
        if ($stmt === false) {
            throw new \Exception(message: $this->db->lastErrorMsg());
        }
        $stmt->bindValue(param: ':_24hour_count', value: $_24_hour['row_count'], type: SQLITE3_NUM);
        $stmt->bindValue(param: ':_24hour_amount', value: $_24_hour['total_amount'], type: SQLITE3_NUM);
        $stmt->bindValue(param: ':_alltime_count', value: $_alltime['row_count'], type: SQLITE3_NUM);
        $stmt->bindValue(param: ':_alltime_amount', value: $_alltime['total_amount'], type: SQLITE3_NUM);
        $stmt->bindValue(param: ':faucet_balance', value: $balance, type: SQLITE3_NUM);
        $stmt->bindValue(param: ':timestamp', value: $now, type: SQLITE3_INTEGER);
        if($stmt->execute()) {
            return true;
        }
        return false;
    }
    public function getBlacklisted(string $wallet_address, string $ip_address): bool {
        $stmt = $this->db->prepare(query: 'SELECT id FROM blacklist WHERE wallet_address = :wallet_address OR ip_address = :ip_address LIMIT 1');
        if ($stmt === false) {
            throw new \Exception(message: $this->db->lastErrorMsg());
        }
        $stmt->bindValue(param: ':wallet_address', value: $wallet_address);
        $stmt->bindValue(param: ':ip_address', value: $ip_address);
        $result = $stmt->execute()->fetchArray(mode: SQLITE3_ASSOC);
        return $result == false ? false : true;
    }
    
}