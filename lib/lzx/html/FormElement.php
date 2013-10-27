<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace lzx\html;

use lzx\html\HTMLElement;

/**
 * Description of FormElement
 *
 * @author ikki
 * @property $name
 * @property $label
 * @property $help
 * @property $required
 * @property $attributes
 */
abstract class FormElement
{

   const ELEMENT_CLASS = 'form_element';
   const LABEL_CLASS = 'element_label';
   const INPUT_CLASS = 'element_input';
   const REQUIRED_CLASS = 'element_required';
   const HELP_CLASS = 'element_help';

   public $name;
   public $label;
   public $help;
   public $required;
   public $attributes = array();
   protected $_value;
   protected $_inline = FALSE;

   public function __construct($name, $label = '', $value = '', $help = '', $required = FALSE)
   {
      $this->name = $name;
      $this->label = $label;
      $this->help = $help;
      $this->required = $required;
      $this->_value = $value;
   }

   protected function _label()
   {
      if (\strlen($this->label) == 0 || \is_null($this->label))
      {
         $this->_inline = TRUE;
         return NULL;
      }

      if ($this->_inline)
      {
         $label = new HTMLElement('label', $this->label);
      }
      else
      {
         $label = new HTMLElement('div', new HTMLElement('label', $this->label), array('class' => self::LABEL_CLASS));
      }

      if ($this->required)
      {
         $label->setDataByIndex(NULL, new HTMLElement('span', ' * ', array('class' => self::REQUIRED_CLASS)));
      }
      if (strlen($this->help) > 0)
      {
         $label->setDataByIndex(NULL, new HTMLElement('span', ' ? ', array('class' => self::HELP_CLASS, 'title' => $this->help)));
      }
      return $label;
   }

   /**
    *
    * @return \lzx\html\FormElement
    */
   public function inline()
   {
      $this->_inline = TRUE;
      return $this;
   }

   public function getValue()
   {
      return $this->_value;
   }

   /*
    * @return FormElement
    */

   public function setValue($value = NULL)
   {
      if ($value !== NULL)
      {
         $this->_value = $value;
      }
      return $this;
   }

   /*
    * @return HTMLElement
    */

   abstract public function toHTMLElement();

   /*
    * @var HTMLELement $element
    */

   public function __toString()
   {
      $element = $this->toHTMLElement();
      return (string) $element;
   }

}

//__END_OF_FILE__
