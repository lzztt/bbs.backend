<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace lzx\html;

use lzx\html\HTMLElement;
use lzx\html\FormElement;

/**
 * Description of Form
 *
 * @author ikki
 */
class Form extends HTMLElement
{

   public function __construct(array $attributes = [])
   {
      $_attr = [
         'accept-charset' => 'UTF-8',
         'autocomplete' => 'off',
         'method' => 'post',
      ];
      $attributes = \array_merge($_attr, $attributes);
      parent::__construct('form', NULL, $attributes);
   }

   public function setButton(array $buttons)
   {
      $types = ['submit', 'reset'];
      $_buttons = [];
      foreach ($buttons as $type => $text)
      {
         if (\in_array($type, $types))
         {
            $_buttons[] = new HTMLElement('button', $text, ['type' => $type]);
         }
      }
      if (sizeof($_buttons) > 0)
      {
         $div = new HTMLElement('div', NULL, ['class' => FormElement::ELEMENT_CLASS]);
         $div->addElement(new HTMLElement('div', $_buttons, ['class' => FormElement::INPUT_CLASS]));
         $this->addElement($div);
      }
   }

}

//__END_OF_FILE__
