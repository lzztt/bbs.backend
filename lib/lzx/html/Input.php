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

    public function __construct($name, $label = '', $help = '', $required = false)
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
        $attr = ['class' => self::ELEMENT_CLASS];
        if ($this->inline) {
            $attr['style'] = 'display:inline';
        }
        $div = new HTMLElement('div', $this->label(), $attr);

        $this->attributes = array_merge(['size' => ($this->inline ? '10' : '22')], $this->attributes);

        $input_attr = [
            'name' => $this->name,
            'type' => $this->type
        ];
        if ($this->value) {
            $input_attr['value'] = $this->value;
        }
        if ($this->required) {
            $input_attr['required'] = 'required';
        }
        $input = new HTMLElement('input', null, array_merge($this->attributes, $input_attr));

        if ($this->inline) {
            $div->addElement($input);
        } else {
            $div->addElement(new HTMLElement('div', $input, ['class' => self::INPUT_CLASS]));
        }

        return $div;
    }
}

//__END_OF_FILE__
