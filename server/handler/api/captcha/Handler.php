<?php

namespace site\handler\api\captcha;

use site\Service;
use site\Config;
use lzx\core\Response;

class Handler extends Service
{
    public function get()
    {
        if (!( $this->request->referer && $this->args )) {
            $this->response->pageForbidden();
            throw new \Exception();
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

    private function getRandomColor()
    {
        $hex_dark = '#' . mt_rand(0, 9) . mt_rand(0, 9) . mt_rand(0, 9) . mt_rand(0, 9) . mt_rand(0, 9) . mt_rand(0, 9);
        return new \ImagickPixel($hex_dark);
    }

    /**
     * Base public function for generating a image CAPTCHA.
     */
    private function generateImage($code, $format)
    {
        // Get font.
        $config = Config::getInstance();
        $font = $config->path['file'] . '/fonts/Tuffy.ttf';

        // get other settings
        $font_size = 36;
        $cage_width = $font_size;
        $cage_height = $font_size * 1.2;

        $text = new \ImagickDraw();
        $text->setFont($font);
        $text->setFontSize($font_size);
        $text->setGravity(\Imagick::GRAVITY_CENTER);

        // create image resource
        //$image = imagecreatetruecolor($width, $height);
        $cages = new \Imagick();
        foreach ($code as $c) {
            $cages->newimage($cage_width, $cage_height, '#FFFFFF', $format);
            $text->setFillColor($this->getRandomColor());
            $x = mt_rand(-3, 3);
            $y = mt_rand(-8, 8);
            $a = mt_rand(-20, 20);
            $cages->annotateimage($text, $x, $y, $a, $c);
        }
        $cages->rewind();
        $image = $cages->appendimages(false);
        $cages->destroy();

        $w = $image->getimagewidth();
        $h = $image->getimageheight();
        $noise = new \ImagickDraw();
        $noise->setstrokewidth(1);
        $noiseLevel = 6;
        for ($i = 0; $i < $noiseLevel; $i++) {
            $noise->setstrokecolor($this->getRandomColor());
            $noise->line(mt_rand(0, $w), mt_rand(0, $h), mt_rand(0, $w), mt_rand(0, $h));
        }
        $image->drawImage($noise);

        $image->waveImage(3, mt_rand(60, 100));
        $image->addnoiseimage(\imagick::NOISE_IMPULSE);
        return $image;
    }
}
