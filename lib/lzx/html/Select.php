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
    public $options = [];
    public $multiple = false;

    public function __construct($name, $label = '', $help = '', $required = false)
    {
        $value = [];
        parent::__construct($name, $label, $value, $help, $required);
    }

    public function setValue($value = null)
    {
        if (\array_key_exists($value, $this->options)) {
            if ($this->multiple) {
                $this->_value[] = $value;
            } else {
                $this->_value = [$value];
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
        $attr = ['class' => self::ELEMENT_CLASS];
        if ($this->_inline) {
            $attr['style'] = 'display:inline';
        }
        $div = new HTMLElement('div', $this->_label(), $attr);

        if ($this->style == 'list') {
            $type = $this->multiple ? 'checkbox' : 'radio';
            $list = new HTMLElement('ul', null, ['class' => 'select_options']);
            $i = 0;
            foreach ($this->options as $value => $text) {
                $i++;
                $option = new HTMLElement('li');
                $option_id = \implode('_', [$type, $this->name, $i]);
                $option_attr = [
                    'id' => $option_id,
                    'type' => $type,
                    'name' => $this->name,
                    'value' => $value
                ];
                if (\in_array($value, $this->_value)) {
                    $option_attr['checked'] = 'checked';
                }
                $option->addElement(new HTMLElement('option', null, \array_merge($this->attributes, $option_attr)));
                $option->addElement(new HTMLElement('label', $text, ['for' => $option_id]));
                $list->addElement($option);
            }
        } elseif ($this->style == 'dropdown') {
            $attr['name'] = $this->name;
            if ($this->multiple) {
                $attr['multiple'] = 'multiple';
            }
            $list = new HTMLElement('select', null, \array_merge($this->attributes, $attr));
            foreach ($this->options as $value => $text) {
                $option_attr = ['value' => $value];
                if (\in_array($value, $this->_value)) {
                    $option_attr['selected'] = 'selected';
                }
                $option = new HTMLElement('option', $text, $option_attr);
                $list->addElement($option);
            }
        } else {
            throw new \Exception('wrong select element style : ' . $this->style);
        }

        if ($this->_inline) {
            $div->addElement($list);
        } else {
            $div->addElement(new HTMLElement('div', $list, ['class' => self::INPUT_CLASS]));
        }

        return $div;
    }
}

//__END_OF_FILE__
