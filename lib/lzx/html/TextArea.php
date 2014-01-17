<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace lzx\html;

use lzx\html\HTMLElement;
use lzx\html\FormElement;

/**
 * Description of TextArea
 *
 * @author ikki
 */
class TextArea extends FormElement
{

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
      $div = new HTMLElement('div', $this->_label(), ['class' => self::ELEMENT_CLASS]);

      $this->attributes = \array_merge(['rows' => '5', 'cols' => '50'], $this->attributes);

      $input_attr = [
         'name' => $this->name,
      ];
      if ($this->required)
      {
         $input_attr['required'] = 'required';
      }
      $input = new HTMLElement('textarea', $this->_value, \array_merge($this->attributes, $input_attr));

      if ($this->_inline)
      {
         $div->addElement($input);
      }
      else
      {
         $div->addElement(new HTMLElement('div', $input, ['class' => self::INPUT_CLASS]));
      }

      return $div;
   }

}

//__END_OF_FILE__
