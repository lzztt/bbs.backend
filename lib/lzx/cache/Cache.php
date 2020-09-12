<?php declare(strict_types=1);

namespace lzx\cache;

use lzx\cache\CacheHandler;

abstract class Cache
{
    protected CacheHandler $handler;
    protected string $key;
    protected string $data = '';
    protected array $parents = [];
    protected array $children = [];
    protected bool $deleted = false;
    protected bool $dirty = false;

    public function __construct(string $key, CacheHandler $handler)
    {
        $this->key = $handler->cleanKey($key);
        $this->handler = $handler;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getData(): string
    {
        return $this->data;
    }

    public function setData(string $data): void
    {
        $this->data = $data;
        $this->dirty = true;
    }

    public function store(string $data): void
    {
        $this->setData($data);
    }

    public function delete(): void
    {
        $this->data = '';
        $this->dirty = true;
        $this->deleted = true;
    }

    public function addParent(string $key): void
    {
        $key = $this->handler->cleanKey($key);
        if (!in_array($key, $this->parents)) {
            $this->parents[] = $key;
        }
        $this->dirty = true;
    }

    public function addChild(string $key): void
    {
        $key = $this->handler->cleanKey($key);
        if (!in_array($key, $this->children)) {
            $this->children[] = $key;
        }
        $this->dirty = true;
    }

    public function getParents(): array
    {
        return $this->parents;
    }

    public function getChildren(): array
    {
        return $this->children;
    }

    abstract public function flush(): void;
}
