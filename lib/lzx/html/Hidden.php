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
        $attr = ['name' => $this->name, 'value' => $this->value, 'type' => 'hidden'];
        return new HTMLElement('input', null, $attr);
    }
}

//__END_OF_FILE__
