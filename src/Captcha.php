<?php declare(strict_types=1);
namespace Faucet;

use Faucet\Config;

class Captcha {
    public static int $width;
    public static int $height;
    public static int $length;
    public static string $font;
    public static string $imageB64;
    public static string $code;
    public static string $hash;

    public static function init(): void {
        self::$width = Config::get(key: 'captcha_width');
        self::$height = Config::get(key: 'captcha_height');
        self::$length = Config::get(key: 'captcha_length');
        self::$font = __DIR__ . Config::get(key: 'captcha_font');
        self::$code = self::generateCode();
        self::$hash = self::generateHash();
        self::$imageB64 = self::generateImage();
    }

    public static function getCode(): string {
        return self::$code;
    }
    public static function generateCode(): string {
        $chars = 'ABCDEFGHJKMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz0123456789';
        return substr(string: str_shuffle(string: $chars), offset: 0, length: self::$length);
    }

    public static function generateHash(): string {
        return password_hash(password: self::$code, algo: PASSWORD_DEFAULT);
    }
    public static function getHash():string {
        return self::$hash;
    }

    public static function getImage(): string {
        return self::$imageB64;
    }
    public static function validateCaptcha(string $client, string $server): bool {
        if(password_verify(password: $client, hash: $server)) return true;
        return false;
    }

    public static function generateImage(): string {
        $image = imagecreatetruecolor(width: self::$width, height: self::$height);

        $bgColor = imagecolorallocate(image: $image, red: 89, green: 101, blue: 219);
        $textColor = imagecolorallocate(image: $image, red: 255, green: 255, blue: 255);
        $lineColor = imagecolorallocate(image: $image, red: 255, green: 255, blue: 255);
        $dotColor = imagecolorallocate(image: $image, red: 255, green: 255, blue: 255);

        imagefilledrectangle(image: $image, x1: 0, y1: 0, x2: self::$width, y2: self::$height, color: $bgColor);

        for ($i = 0; $i < 500; $i++) {
            imagesetpixel(image: $image, x: rand(min: 0, max: self::$width), y: rand(min: 0, max: self::$height), color: $dotColor);
        }

        for ($i = 0; $i < 10; $i++) {
            imageline(image: $image, x1: rand(min: 0, max: self::$width), y1: rand(min: 0, max: self::$height), x2: rand(min: 0, max: self::$width), y2: rand(min: 0, max: self::$height), color: $lineColor);
        }

        $fontSize = self::$height * 0.5;
        $x = 10;
        $y = self::$height * 0.7;

        for ($i = 0; $i < strlen(string: self::$code); $i++) {
            imagettftext(image: $image, size: $fontSize, angle: rand(min: -15, max: 15), x: (int)$x, y: (int)$y, color: $textColor, font_filename: self::$font, text: self::$code[$i]);
            $x += self::$width / self::$length;
        }

        ob_start();
        imagepng(image: $image, file: null);
        $img = ob_get_contents();
        ob_end_clean();

        return 'data:image/png;base64,' . base64_encode(string: $img);
    }
}
