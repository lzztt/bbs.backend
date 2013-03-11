<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace lzx\html;

use lzx\html\HTMLElement;
use lzx\html\FormElement;

/**
 * Description of Select
 *
 * @author ikki
 */
class Select extends FormElement
{

   //put your code here
   public $style = 'dropdown'; // list or dropdown
   public $options = array();
   public $multiple = FALSE;

   public function __construct($name, $label = '', $help = '', $required = FALSE)
   {
      $value = array();
      parent::__construct($name, $label, $value, $help, $required);
   }

   public function setValue($value = NULL)
   {
      if (\array_key_exists($value, $this->options))
      {
         if ($this->multiple)
         {
            $this->_value[] = $value;
         }
         else
         {
            $this->_value = array($value);
         }
      }
      return $this;
   }

   /**
    *
    * @return \lzx\html\HTMLElement
    * @throws \Exception
    */
   public function toHTMLElement()
   {
      $attr = array('class' => self::ELEMENT_CLASS);
      if ($this->_inline)
      {
         $attr['style'] = 'display:inline';
      }
      $div = new HTMLElement('div', $this->_label(), $attr);

      if ($this->style == 'list')
      {
         $type = $this->multiple ? 'checkbox' : 'radio';
         $list = new HTMLElement('ul', NULL, array('class' => 'select_options'));
         $i = 0;
         foreach ($this->options as $value => $text)
         {
            $i++;
            $option = new HTMLElement('li');
            $input_id = \implode('_', array($type, $this->name, $i));
            $input_attr = array(
               'id' => $input_id,
               'type' => $type,
               'name' => $this->name,
               'value' => $value
            );
            if (in_array($value, $this->_value))
            {
               $input_attr['checked'] = 'checked';
            }
            $option->addElements(new HTMLElement('input', NULL, array_merge($this->attributes, $input_attr)));
            $option->addElements(new HTMLElement('label', $text, array('for' => $input_id)));
            $list->addElements($option);
         }
      }
      elseif ($this->style == 'dropdown')
      {
         $attr['name'] = $this->name;
         if ($this->multiple)
         {
            $attr['multiple'] = 'multiple';
         }
         $list = new HTMLElement('select', NULL, array_merge($this->attributes, $attr));
         foreach ($this->options as $value => $text)
         {
            $option_attr['value'] = $value;
            if (in_array($value, $this->_value))
            {
               $option_attr['selected'] = 'selected';
            }
            $option = new HTMLElement('option', $text, $option_attr);
            $list->addElements($option);
         }
      }
      else
      {
         throw new \Exception('wrong select element style : ' . $this->style);
      }

      if ($this->_inline)
      {
         $div->addElements($list);
      }
      else
      {
         $div->addElements(new HTMLElement('div', $list, array('class' => self::INPUT_CLASS)));
      }

      return $div;
   }

}

//__END_OF_FILE__
