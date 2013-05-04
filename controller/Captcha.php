<?php

namespace site\controller;

use lzx\core\Controller;
use lzx\html\Template;

class Captcha extends Controller
{

   public function run()
   {
      if (strpos($_SERVER['HTTP_REFERER'], $this->request->domain) < 4)
      {
         $this->logger->info('Captcha Access Error: Wrong Referer : ' . $this->request->uri . ', from: ' . $_SERVER['HTTP_REFERER']);
         $this->request->pageForbidden();
      }

      // generate a CAPTCHA code
      $chars = 'aABCdEeFfGHKLMmNPRSTWXY23456789';
      $code_length = 5;
      $code = \substr(\str_shuffle($chars), 0, $code_length);
      $this->session->captcha = $code;

      // generate the image
      $image = $this->_generate_image(\str_split($code), 'jpeg');
      header('Content-type: image/jpeg');
      exit($image);
   }

   public static function checkCaptcha()
   {
      if (\strtolower($this->session->captcha) != \strtolower($this->request->post['captcha']))
      {
         return FALSE;
      }

      unset($this->session->captcha, $this->request->post['captcha']);
      return TRUE;
   }

   private function _get_rand_color()
   {
      $hex_dark = '#' . \mt_rand(0, 9) . \mt_rand(0, 9) . \mt_rand(0, 9) . \mt_rand(0, 9) . \mt_rand(0, 9) . \mt_rand(0, 9);
      return new \ImagickPixel($hex_dark);
   }

   /**
    * Base public function for generating a image CAPTCHA.
    */
   private function _generate_image($code, $format)
   {
      // Get font.
      $font = $this->path['file'] . '/themes/' . Template::$theme . '/images/Tuffy.ttf';

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
      foreach ($code as $c)
      {
         $cages->newimage($cage_width, $cage_height, '#FFFFFF', $format);
         $text->setFillColor($this->_get_rand_color());
         $x = \mt_rand(-3, 3);
         $y = \mt_rand(-8, 8);
         $a = \mt_rand(-20, 20);
         $cages->annotateimage($text, $x, $y, $a, $c);
      }
      $cages->rewind();
      $image = $cages->appendimages(FALSE);
      $cages->destroy();

      $w = $image->getimagewidth();
      $h = $image->getimageheight();
      $noise = new \ImagickDraw();
      $noise->setstrokewidth(1);
      $noiseLevel = 6;
      for ($i = 0; $i < $noiseLevel; $i++)
      {
         $noise->setstrokecolor($this->_get_rand_color());
         $noise->line(\mt_rand(0, $w), \mt_rand(0, $h), \mt_rand(0, $w), \mt_rand(0, $h));
      }
      $image->drawImage($noise);

      $image->waveImage(3, \mt_rand(60, 100));
      $image->addnoiseimage(\imagick::NOISE_IMPULSE);
      return $image;
   }

}

//__END_OF_FILE__