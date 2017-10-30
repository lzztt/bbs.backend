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
    public $attributes = [];
    protected $value;
    protected $inline = false;

    public function __construct($name, $label = '', $value = '', $help = '', $required = false)
    {
        $this->name = $name;
        $this->label = $label;
        $this->help = $help;
        $this->required = $required;
        $this->value = $value;
    }

    protected function label()
    {
        if (strlen($this->label) == 0 || is_null($this->label)) {
            $this->inline = true;
            return null;
        }

        if ($this->inline) {
            $label = new HTMLElement('label', $this->label);
        } else {
            $label = new HTMLElement('div', new HTMLElement('label', $this->label), ['class' => self::LABEL_CLASS]);
        }

        if ($this->required) {
            $label->setDataByIndex(null, new HTMLElement('span', ' * ', ['class' => self::REQUIRED_CLASS]));
        }
        if (strlen($this->help) > 0) {
            $label->setDataByIndex(null, new HTMLElement('span', ' ? ', ['class' => self::HELP_CLASS, 'title' => $this->help]));
        }
        return $label;
    }

    /**
     *
     * @return \lzx\html\FormElement
     */
    public function inline()
    {
        $this->inline = true;
        return $this;
    }

    public function getValue()
    {
        return $this->value;
    }

    /*
     * @return FormElement
     */

    public function setValue($value = null)
    {
        if ($value !== null) {
            $this->value = $value;
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
