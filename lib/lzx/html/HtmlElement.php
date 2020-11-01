<?php declare(strict_types=1);

namespace lzx\html;

use Exception;

// $data could be NULL, a string, or HtmlElement object, or an array of strings or HtmlElement objects
class HtmlElement extends Template
{
    protected string $tag;
    protected array $data;
    protected array $attributes;

    public function __construct(string $tag = '', $data = null, array $attributes = [])
    {
        if (!$tag && count($attributes) > 0) {
            throw new Exception('HtmlElement Fragment does not support attributes.');
        }

        $this->tag = $tag;
        $this->data = [];
        $this->addData($data);
        $this->attributes = $attributes;
    }

    public function addData($data): void
    {
        if ($this->cache) {
            throw new Exception(self::FINALIZED);
        }

        if (is_array($data)) {
            foreach ($data as $element) {
                $this->addDataElement($element);
            }
        } else {
            $this->addDataElement($data);
        }
    }

    // add a single data element
    protected function addDataElement($data): void
    {
        if ($data) {
            if (!($data instanceof self || is_string($data))) { // not string or HtmlElement object
                throw new Exception('wrong data type (string, ' . __CLASS__ . ') : ' . gettype($data));
            }
            $this->data[] = $data;
        }
    }

    public function __toString()
    {
        if (!$this->cache) {
            if (!$this->tag) {
                $this->cache = implode('', $this->data);
            } elseif (empty($this->data)) {
                $this->cache = '<' . $this->tag . $this->attr() . ' />';
            } else {
                $this->cache = '<' . $this->tag . $this->attr() . '>' . implode('', $this->data) . '</' . $this->tag . '>';
            }
        }
        return $this->cache;
    }

    protected function attr(): string
    {
        $attr = '';

        foreach ($this->attributes as $k => $v) {
            $attr .= ' ' . htmlspecialchars($k) . '="' . htmlspecialchars($v) . '"';
        }

        return $attr;
    }

    public static function link(string $name, string $url, array $attributes = []): self
    {
        $attributes['href'] = $url;
        return new self('a', $name, $attributes);
    }

    public static function breadcrumb(array $links): self
    {
        $list = [];
        $count = count($links) - 1;
        foreach ($links as $text => $uri) {
            $list[] = $count-- ? self::link($text, $uri) : (string) $text;
        }

        return new self('nav', $list, ['class' => 'breadcrumb']);
    }

    public static function pager(int $pageNo, int $pageCount, string $uri): self
    {
        if ($pageCount < 2) {
            return new self();
        }

        if ($pageCount <= 5) {
            $pageFirst = 1;
            $pageLast = $pageCount;
        } else {
            $pageFirst = $pageNo - 2;
            $pageLast = $pageNo + 2;
            if ($pageFirst < 1) {
                $pageFirst = 1;
                $pageLast = 5;
            } elseif ($pageLast > $pageCount) {
                $pageFirst = $pageCount - 4;
                $pageLast = $pageCount;
            }
        }

        if ($pageNo != 1) {
            $pager[] = self::link('<<', $uri);
        }
        for ($i = $pageFirst; $i <= $pageLast; $i++) {
            if ($i == $pageNo) {
                $pager[] = self::link((string) $i, $uri . '?p=' . $i, ['class' => 'active']);
            } else {
                $pager[] = self::link((string) $i, $uri . '?p=' . $i);
            }
        }
        if ($pageNo != $pageCount) {
            $pager[] = self::link('>>', $uri . '?p=' . $pageCount);
        }
        return new self('nav', $pager, ['class' => 'pager']);
    }
}
