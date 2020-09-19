<?php declare(strict_types=1);

namespace lzx\geo;

class Name
{
    public $en;
    public $zh;

    public function __construct(string $en, string $zh)
    {
        $this->en = $en;
        $this->zh = $zh;
    }

    public function isEmpty(): bool
    {
        return $this->en === '' && $this->zh === '';
    }
}
