<?php declare(strict_types=1);
namespace Faucet;
//Autoload classes
require_once __DIR__ . "/vendor/autoload.php";

//Comment out in prod
//ini_set( 'session.cookie_httponly', 1 );
//header(header: "Content-Security-Policy: default-src 'self' 'unsafe-inline' http://localhost; script-src 'self' http://localhost; script-src-elem 'self' http://localhost; style-src 'self' http://localhost https://fonts.googleapis.com; style-src-elem 'self' http://localhost https://fonts.googleapis.com; style-src-attr 'self' http://localhost; img-src 'self' data: http://localhost; font-src http://localhost fonts.gstatic.com; connect-src 'none'; media-src 'none'; object-src 'none'; prefetch-src 'none'; child-src 'none'; frame-src 'none'; worker-src 'none'; frame-ancestors 'self' http://localhost; form-action 'self' http://localhost; upgrade-insecure-requests; block-all-mixed-content; sandbox allow-forms allow-same-origin; base-uri 'self' http://localhost; manifest-src 'none';");

//Setup Configuration
$config = new Config();

//Setup Session
session_start();
$session = new Session();

date_default_timezone_set(timezoneId: $config->get(key: 'timezone'));       //Timezone

//Setup error/exception handling
if($config->get(key: 'production', default:true) === false) {                //Prod/Debug Mode
    ini_set(option: 'display_errors', value: 1);
    ini_set(option: 'display_startup_errors', value: 1);
    error_reporting(error_level: E_ALL);
    //Comment these lines out on production server
    //ini_set(option: 'log_errors', value: '1');
    //ini_set(option: 'error_log', value: 'error.log');
    set_exception_handler(callback: [ExceptionHandler::class , 'handleExceptionDebug']);
} else {
    //Comment these lines out on production server
    //ini_set(option: 'log_errors', value: '1');
    //ini_set(option: 'error_log', value: 'error.log');
    error_reporting(error_level: E_ALL);
    set_exception_handler(callback: [ExceptionHandler::class , 'handleExceptionProduction']);
}

//Route Request
$router = new Router();

$data = AppDataProvider::getData();

if ($config->get(key: 'glockdown', default: false)) { 
    $router->route(state: State::Error, data: [
        'message' => Config::get(key: 'glockdown_msg', default: 'Faucet is offline'),
        'mode' => Config::get(key: 'production') ? 'prod' : 'debug',
    ] + $data); 
    exit;
}

$router->route(state: State::Welcome, data: $data); exit;

