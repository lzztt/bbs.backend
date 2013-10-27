<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace lzx\html;

use lzx\html\HTMLElement;
use lzx\html\FormElement;

/**
 * Description of Hidden
 *
 * @author ikki
 */
class Hidden extends FormElement
{

   public function __construct($name, $value = '')
   {
      parent::__construct($name, '', $value);
   }

   /**
    *
    * @return \lzx\html\HTMLElement
    */
   public function toHTMLElement()
   {
      $attr = array('name' => $this->name, 'value' => $this->_value, 'type' => 'hidden');
      return new HTMLElement('input', NULL, $attr);
   }

}

//__END_OF_FILE__
