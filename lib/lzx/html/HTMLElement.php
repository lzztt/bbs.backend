<?php declare(strict_types=1);

namespace lzx\html;

use Exception;

// $data could be NULL, a string, or Element object, or an array of strings or Element objects
class HTMLElement
{
    protected $tag;
    protected $data;
    protected $attributes;

    public function __construct(string $tag, $data = null, array $attributes = [])
    {
        if (!$tag) {
            throw new Exception('wrong tag name (should be a non-empty string) : ' . gettype($tag));
        }

        $this->tag = $tag;
        $this->data = [];
        if ($data) {
            $this->addData($data);
        }
        $this->attributes = $attributes;
    }

    public function addData($data): void
    {
        if (is_array($data)) {
            foreach ($data as $element) {
                 $this->addDataElement($element);
            }
        } else {
            $this->addDataElement($data);
        }
    }

    // set a single data element to an index
    protected function addDataElement($data): void
    {
        if ($data) {
            if (!($data instanceof self || is_string($data))) { // not string or Element object
                throw new Exception('wrong data type (string, ' . __CLASS__ . ') : ' . gettype($data));
            }
            $this->data[] = $data;
        }
    }

    public function __toString()
    {
        if (is_null($this->data)) {
              return '<' . $this->tag . $this->attr() . ' />';
        }
        if (is_string($this->data) || $this->data instanceof self) {
            return '<' . $this->tag . $this->attr() . '>' . $this->data . '</' . $this->tag . '>';
        }
        if (is_array($this->data)) {
            return '<' . $this->tag . $this->attr() . '>' . implode('', $this->data) . '</' . $this->tag . '>';
        }
    }

    protected function attr(): string
    {
        $attr = '';

        foreach ($this->attributes as $k => $v) {
            $attr .= ' ' . htmlspecialchars($k) . '="' . htmlspecialchars($v) . '"';
        }

        return $attr;
    }
}
