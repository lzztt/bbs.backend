<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace lzx\html;

use lzx\html\HTMLElement;
use lzx\html\FormElement;

/**
 * Description of Input
 *
 * @author ikki
 */
class Input extends FormElement
{

   //put your code here
   public $type = 'text';

   public function __construct($name, $label = '', $help = '', $required = FALSE)
   {
      $value = '';
      parent::__construct($name, $label, $value, $help, $required);
   }

   /**
    *
    * @return \lzx\html\HTMLElement
    */
   public function toHTMLElement()
   {
      $attr = array('class' => self::ELEMENT_CLASS);
      if ($this->_inline)
      {
         $attr['style'] = 'display:inline';
      }
      $div = new HTMLElement('div', $this->_label(), $attr);

      $this->attributes = array_merge(array('size' => ($this->_inline ? '10' : '22')), $this->attributes);

      $input_attr = array(
         'name' => $this->name,
         'type' => $this->type
      );
      if ($this->_value)
      {
         $input_attr['value'] = $this->_value;
      }
      if ($this->required)
      {
         $input_attr['required'] = 'required';
      }
      $input = new HTMLElement('input', NULL, array_merge($this->attributes, $input_attr));

      if ($this->_inline)
      {
         $div->addElements($input);
      }
      else
      {
         $div->addElements(new HTMLElement('div', $input, array('class' => self::INPUT_CLASS)));
      }

      return $div;
   }

}

//__END_OF_FILE__
