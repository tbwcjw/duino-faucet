<? declare(strict_types=1);
namespace Faucet;
class Config {
    private static array $config = [];
    private static array $synonyms = [];
    public function __construct() {
            
        self::$config = [
            "glockdown" => false,
            "glockdown_msg" => "Faucet is down during maintenance.",
            "production" => true,
            "timezone" => "America/New_York",
            "database_path" => "/../database/app.db",
            "logExceptions" => true,
            "transactionAmount" => 0.5,
            "transactionMessage" => "Thanks for using tbwcjw faucet!",
            "cooldownDelta" => "+12 hours",
            "captcha_width" => 150,
            "captcha_height" => 50,
            "captcha_length" => 4,
            "captcha_font" => '/../assets/firasans.ttf',
            "css" => '/../assets/style.css',
            "js" => '/../assets/js/app.js',
            "stats_cache_enabled" => true,
            "stats_cache_invalidate_delta" => "+10 minutes",
            "api_server" => "https://server.duinocoin.com/",
            //"faucet_username" => "",
            //"faucet_password" => ""        
            "faucet_username" => $_ENV['faucet_username'] ?? throw new \Exception(message: "Config exception"),
            "faucet_password" => $_ENV['faucet_password'] ?? throw new \Exception(message: "Config exception")      
        ];
        self::$synonyms = [
            "nozzle", "valve", "hydrant", "spout", "stopcock", "tap", "bibb", "bibcock", "spigot", "nozzle", "sillcock"
        ];
    }

    public static function get(string $key, mixed $default = null): string|int|float|bool {
        return self::$config[$key] ?? $default;
    }
    public static function getSynonym():string {
        return self::$synonyms[array_rand(array: self::$synonyms, num: 1)];
    }
    public static function has(string $key): bool {
        return array_key_exists(key: $key, array: self::$config);
    }
}
