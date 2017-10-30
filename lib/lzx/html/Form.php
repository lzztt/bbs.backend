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
        $attr = [
            'accept-charset' => 'UTF-8',
            'autocomplete' => 'off',
            'method' => 'post',
        ];
        $attributes = array_merge($attr, $attributes);
        parent::__construct('form', null, $attributes);
    }

    public function setButton(array $buttons)
    {
        $types = ['submit', 'reset'];
        $buttons = [];
        foreach ($buttons as $type => $text) {
            if (in_array($type, $types)) {
                $buttons[] = new HTMLElement('button', $text, ['type' => $type]);
            }
        }
        if (sizeof($buttons) > 0) {
            $div = new HTMLElement('div', null, ['class' => FormElement::ELEMENT_CLASS]);
            $div->addElement(new HTMLElement('div', $buttons, ['class' => FormElement::INPUT_CLASS]));
            $this->addElement($div);
        }
    }
}

//__END_OF_FILE__
