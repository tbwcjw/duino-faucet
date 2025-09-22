<?php declare(strict_types=1);
namespace Faucet;

use Faucet\Session;
use Faucet\Config;
use Faucet\Router;
use Faucet\State;

class ExceptionHandler {
    private static Router $router;
    private static \DateTime|string $timestamp;
    public static string $message;
    public static string $file;
    public static int $line;
    public static array $code;
    public static string $trace;

    public static function handleExceptionDebug(?\Throwable $exception): void {
        self::$router = new Router();
        self::$timestamp = date(format: 'Y-m-d H:i:s');
        self::$file = $exception->getFile();
        self::$line = $exception->getLine();
        self::$code = self::getCode(file: self::$file, line: self::$line);
        self::$trace = $exception->getTraceAsString();
        self::$message = self::buildMessage(protoMessage: $exception->getMessage());
        if(Config::get(key: "logExceptions", default: false) === true) self::logException();
        self::$router->route(state: State::Error, data: [
                                                        'message' => self::$message, 
                                                        'timestamp' => self::$timestamp, 
                                                        'file' => self::$file, 
                                                        'line' => self::$line, 
                                                        'code' => self::$code, 
                                                        'trace' => self::$trace]);
    }
    public static function handleExceptionProduction(?\Throwable $exception): void {
        self::$router = new Router();
        self::$timestamp = date(format: 'Y-m-d H:i:s');
        self::$file = $exception->getFile();
        self::$line = $exception->getLine();
        self::$message = $exception->getMessage();
        
        if(Config::get(key: "logExceptions", default: false) === true) self::logException();
        self::$router->route(state: State::Error, data: [
                                                        'message' => self::$message, 
                                                        'timestamp' => self::$timestamp, 
                                                        'file' => self::$file, 
                                                        'line' => self::$line]);
    }
    public static function buildMessage($protoMessage):string {
        return  $protoMessage . " - " . self::$file . " on line: " . self::$line;
    }
    public static function logException(): void {
        error_log(message: Session::getIP() . " " . self::$message, message_type: 0);
    }
    public static function getCode(string $file, int $line): array {
        $lines = file(filename: $file);
        $start = max( 0, $line - 5);
        $end = min(count(value: $lines), $line + 5); 

        $snippet = [];
        for($i=$start;$i<$end;$i++) {
            $lineNumber = $i + 1;
            $highlight = ($lineNumber === $line);
            $snippet[] = ['line_num' => $lineNumber, 'line' => $lines[$i], 'highlight' => $highlight];
        }
        return $snippet;
    }
}