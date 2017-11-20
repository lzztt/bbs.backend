<?php declare(strict_types=1);

namespace lzx\html;

use Exception;

// $data could be NULL, a string, or Element object, or an array of strings or Element objects
class HTMLElement
{
    protected $tag;
    protected $data;
    protected $attributes;

    public function __construct($tag, $data = null, array $attributes = [])
    {
        if (!is_string($tag) || empty($tag)) {
              throw new Exception('wrong tag name (should be a non-empty string) : ' . gettype($tag));
        }

          // type hinting force attributes is always an array

            $this->tag = $tag;
          // allow to set empty string value!!!, otherwise textarea will break
        if (isset($data)) {
            $this->setData($data);
        }
            $this->attributes = $attributes;
    }

    public function setData($data)
    {
         $this->data = null;
        if (is_array($data)) {
            foreach ($data as $element) {
                 $this->setDataByIndex(null, $element);
            }
        } else {
            $this->setDataByIndex(null, $data);
        }
    }

    // set a single data element to an index
    public function setDataByIndex($index, $data)
    {
        if (!($data instanceof self || is_string($data) || is_null($data))) { // not string or Element object or NULL
            throw new Exception('wrong data type (NULL, string, ' . __CLASS__ . ') : ' . gettype($data));
        }

          // reset only if $index is a valid index
        if (is_int($index) && is_array($this->data)) { // set value with index
            if (is_null($data)) {  // NULL value
                if (array_key_exists($index, $this->data)) { // unset element if exist
                     unset($this->data[$index]);
                }
            } else // set/reset value if NOT NULL
            {
                $this->data[$index] = $data;
            }
        } else // otherwise, append to the end of $this->data
            {
            if (isset($data)) { // not NULL
                if (is_null($this->data)) { // current NULL
                     $this->data = $data;
                } elseif (is_array($this->data)) { // current Array
                    $this->data[] = $data;
                } else // current single string or Element
                 {
                    $this->data = [$this->data, $data];
                }
            }
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

    protected function attr()
    {
         $attr = '';

        foreach ($this->attributes as $k => $v) {
              $attr .= ' ' . htmlspecialchars($k) . '="' . htmlspecialchars($v) . '"';
        }

            return $attr;
    }
}
