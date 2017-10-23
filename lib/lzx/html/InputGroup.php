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
    protected $elements = [];

    public function __construct($label, $help = '', $required = false)
    {
         parent::__construct('', $label, '', $help, $required);
    }

    public function addFormElement(FormElement $e)
    {
        if ($e instanceof FormElement) {
              $this->elements[] = $e;
        } else {
            throw new \ErrorException('wrong from element type (FormElement) : ' . \gettype($e));
        }
    }

    public function addFormElements(array $elements)
    {
        foreach ($elements as $e) {
              $this->addFormElement($e);
        }
    }

     /**
      *
      * @return \lzx\html\HTMLElement
      */
    public function toHTMLElement()
    {
         $div = new HTMLElement('div', $this->_label(), ['class' => self::ELEMENT_CLASS]);

         $input = [];
        foreach ($this->elements as $e) {
              $input[] = $e->toHTMLElement();
        }

        if ($this->_inline) {
            $div->setData($input);
        } else {
            $div->addElement(new HTMLElement('div', $input, ['class' => self::INPUT_CLASS]));
        }

            return $div;
    }
}
