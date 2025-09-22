<?php declare(strict_types=1);
namespace Faucet;
class View {
    public static function render(string $view, array $data=[]): void {
        extract( array: $data);
        include("views/$view.php");
    }
}