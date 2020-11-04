<?php

declare(strict_types=1);

namespace lzx\html;

class Template
{
    protected const FINALIZED = 'Template has already been finalized.';

    protected array $data = [];
    protected array $onBeforeRender = [];
    protected string $cache = '';

    public static function fromStr(string $data): self
    {
        return new self(trim($data));
    }

    protected function __construct(string $data)
    {
        $this->cache = $data;
    }

    public function __toString()
    {
        return $this->cache;
    }
}
