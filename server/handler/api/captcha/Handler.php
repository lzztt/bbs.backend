<?php declare(strict_types=1);

namespace site\handler\api\captcha;

use Exception;
use Imagick;
use ImagickDraw;
use ImagickPixel;
use lzx\core\Response;
use site\Config;
use site\Service;

class Handler extends Service
{
    public function get(): void
    {
        if (!($this->request->referer && $this->args)) {
            $this->response->pageForbidden();
            throw new Exception();
        }

        // generate a CAPTCHA code
        $chars = 'aABCdEeFfGHKLMmNPRSTWXY23456789';
        $code_length = 5;
        $code = substr(str_shuffle($chars), 0, $code_length);
        $this->session->captcha = $code;

        // generate the image
        $this->response->type = Response::JPEG;
        $this->response->setContent($this->generateImage(str_split($code), 'jpeg'));
    }

    private function getRandomColor(): ImagickPixel
    {
        $hex_dark = '#' . rand(0, 9) . rand(0, 9) . rand(0, 9) . rand(0, 9) . rand(0, 9) . rand(0, 9);
        return new ImagickPixel($hex_dark);
    }

    private function generateImage(array $code, string $format): Imagick
    {
        // Get font.
        $config = Config::getInstance();
        $font = $config->path['file'] . '/fonts/Tuffy.ttf';

        // get other settings
        $font_size = 36;
        $box_width = $font_size;
        $box_height = (int) ($font_size * 1.2);

        $text = new ImagickDraw();
        $text->setFont($font);
        $text->setFontSize($font_size);
        $text->setGravity(Imagick::GRAVITY_CENTER);

        // create image resource
        //$image = imagecreatetruecolor($width, $height);
        $boxes = new Imagick();
        foreach ($code as $c) {
            $boxes->newimage($box_width, $box_height, '#FFFFFF', $format);
            $text->setFillColor($this->getRandomColor());
            $x = rand(-3, 3);
            $y = rand(-8, 8);
            $a = rand(-20, 20);
            $boxes->annotateimage($text, $x, $y, $a, $c);
        }
        $boxes->rewind();
        $image = $boxes->appendimages(false);
        $boxes->destroy();

        $w = $image->getimagewidth();
        $h = $image->getimageheight();
        $noise = new ImagickDraw();
        $noise->setstrokewidth(1);
        $noiseLevel = 6;
        for ($i = 0; $i < $noiseLevel; $i++) {
            $noise->setstrokecolor($this->getRandomColor());
            $noise->line(rand(0, $w), rand(0, $h), rand(0, $w), rand(0, $h));
        }
        $image->drawImage($noise);

        $image->waveImage(3, rand(60, 100));
        $image->addnoiseimage(Imagick::NOISE_IMPULSE);
        return $image;
    }
}
