<?php declare(strict_types=1);
namespace Faucet;

use Faucet\API;
use Faucet\FaucetController;
use Faucet\Statistics;
use Faucet\View;
use Faucet\AppDataProvider;
use Faucet\Config;

enum State: string {
    case Welcome = 'welcome';
    case Error = 'error';
}

class Router {
    private $controller;
    public function __construct() {
        $this->controller = new FaucetController();
    }
    public function route(State $state, $data = []): void {
        if(ob_get_contents()) ob_clean();
        switch ($state) {
            case State::Welcome:
                $status = null;
                API::init();
                Statistics::init();
                $stats = Statistics::getCachedStatistics();
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $status = $this->controller->tryReward(
                        wallet_address: $_POST['wallet_address'],
                        captcha_answer: $_POST['captcha_answer'],
                        captcha_hash: $_POST['captcha_token'],
                        csrf_token: $_POST['csrf_token']
                    );
                }
        
                View::render(
                    view: "welcome",
                    data: AppDataProvider::getData(stats: $stats, status: $status)
                );
                break;
        
            case State::Error:
                View::render(
                    view: 'error',
                    data: [
                        'error' => $data,
                        'mode' => Config::get(key: 'production') ? 'prod' : 'debug',
                    ] + AppDataProvider::getData(stats: null),
                );
                break;
        }
    }
}
