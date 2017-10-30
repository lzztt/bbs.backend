<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace lzx\html;

/**
 * Description of HTML
 *
 * @author ikki
 * $data could be NULL, a string, or Element object, or an array of strings or Element objects
 */
class HTMLElement
{
    protected $tag;
    protected $data;
    protected $attributes;

    public function __construct($tag, $data = null, array $attributes = [])
    {
        if (!is_string($tag) || empty($tag)) {
              throw new \Exception('wrong tag name (should be a non-empty string) : ' . gettype($tag));
        }

          // type hinting force attributes is always an array

            $this->tag = $tag;
          // allow to set empty string value!!!, otherwise textarea will break
        if (isset($data)) {
            $this->setData($data);
        }
            $this->attributes = $attributes;
    }

    public function getTag()
    {
         return $this->tag;
    }

    public function getData()
    {
         return $this->data;
    }

     /**
      *
      * @param NULL,string,HTMLElement,array $data
      */
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

     /**
      *
      * @param int $index
      * @return null
      */
    public function getDataByIndex($index)
    {
         // passed an index
        if (array_key_exists($index, $this->data)) {
              return $this->data[$index];
        } else {
            return null;
        }
    }

     // set a single data element to an index
     /**
      *
      * @param int $index
      * @param NULL/string/HTMLElement $data
      * @throws \Exception
      */
    public function setDataByIndex($index, $data)
    {
        if (!($data instanceof self || is_string($data) || is_null($data))) { // not string or Element object or NULL
            throw new \Exception('wrong data type (NULL, string, ' . __CLASS__ . ') : ' . gettype($data));
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

    public function getAttributes()
    {
         return $this->attributes;
    }

     /**
      *
      * @param array $attributes
      */
    public function setAttributes($attributes)
    {
         $this->attributes = [];
        foreach ($attributes as $k => $v) {
              $this->setAttributeKeyOf($k, $v);
        }
    }

     /**
      *
      * @param string $key
      * @return null
      */
    public function getAttributeKeyOf($key)
    {
        if (array_key_exists($key, $this->attributes)) {
              return $this->attributes[$key];
        } else {
            return null;
        }
    }

     /**
      *
      * @param string $key
      * @param string $value
      * @throws \Exception
      */
    public function setAttributeKeyOf($key, $value)
    {
        if (strlen($key) > 0 && (is_string($value) || is_null($value))) {
            if (is_null($value)) { // NULL value
                if (array_key_exists($key, $this->attributes)) { // unset if key exist
                    unset($this->attributes[$key]);
                }
            } else // set/reset value if NOT NULL
                {
                 $this->attributes[$key] = $value;
            }
        } else {
            throw new \Exception('wrong attribute type or value (string => string) : ' . $this->tag . '.' . $key . ' => ' . gettype($value));
        }
    }

     /**
      *
      * @param HTMLElement $e
      * @throws \ErrorException
      */
    public function addElement(HTMLElement $e)
    {
        if ($e instanceof HTMLElement) {
              $this->setDataByIndex(null, $e);
        } else {
            throw new \ErrorException('wrong html element type (HTMLElement) : ' . gettype($e));
        }
    }

    public function addElements(array $elements)
    {
        foreach ($elements as $e) {
              $this->addElement($e);
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

//__END_OF_FILE__
