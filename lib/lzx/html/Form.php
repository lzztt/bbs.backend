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

   public function __construct(array $attributes = array())
   {
      $_attr = array(
         'accept-charset' => 'UTF-8',
         'autocomplete' => 'off',
         'method' => 'post',
      );
      $attributes = \array_merge($_attr, $attributes);
      parent::__construct('form', NULL, $attributes);
   }

   public function setButton(array $buttons)
   {
      $types = array('submit', 'reset');
      $_buttons = array();
      foreach ($buttons as $type => $text)
      {
         if (\in_array($type, $types))
         {
            $_buttons[] = new HTMLElement('button', $text, array('type' => $type));
         }
      }
      if (sizeof($_buttons) > 0)
      {
         $div = new HTMLElement('div', NULL, array('class' => FormElement::ELEMENT_CLASS));
         $div->addElements(new HTMLElement('div', $_buttons, array('class' => FormElement::INPUT_CLASS)));
         $this->addElements($div);
      }
   }

}

//__END_OF_FILE__
