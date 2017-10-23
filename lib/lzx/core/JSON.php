<?php

namespace lzx\core;

class JSON
{
    private $_data;
    private $_string;

    public function __construct(array $data = null)
    {
        $this->setData($data);
    }

    public function setData(array $data = null)
    {
        if ($data) {
            $this->_data = $data;
            $this->_string = null;
        } else {
            $this->_data = [];
            $this->_string = '{}';
        }
    }

    public function hasError()
    {
        return \array_key_exists('error', $this->_data) ? (bool) $this->_data['error'] : false;
    }

    public function __toString()
    {
        // string cache
        if (!$this->_string) {
            $this->_string = \json_encode($this->_data, \JSON_NUMERIC_CHECK | \JSON_UNESCAPED_SLASHES | \JSON_UNESCAPED_UNICODE);
            if ($this->_string === false) {
                $this->_string = '{"error":"json encode error"}';
            }
        }
        return $this->_string;
    }
}

//__END_OF_FILE__
