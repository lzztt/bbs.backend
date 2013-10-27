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
class InputGroup extends FormElement
{

   protected $elements = array();

   public function __construct($label, $help = '', $required = FALSE)
   {
      parent::__construct('', $label, '', $help, $required);
   }

   public function addFormElemements()
   {
      $this->elements = \array_merge($this->elements, \func_get_args());
   }

   /**
    *
    * @return \lzx\html\HTMLElement
    */
   public function toHTMLElement()
   {
      $div = new HTMLElement('div', $this->_label(), array('class' => self::ELEMENT_CLASS));

      $input = array();
      foreach ($this->elements as $e)
      {
         $input[] = $e->toHTMLElement();
      }

      if ($this->_inline)
      {
         $div->setData($input);
      }
      else
      {
         $div->addElements(new HTMLElement('div', $input, array('class' => self::INPUT_CLASS)));
      }

      return $div;
   }

}

?>
