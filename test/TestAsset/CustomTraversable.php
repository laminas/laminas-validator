<?php

namespace LaminasTest\Validator\TestAsset;

use Iterator;

use function current;
use function key;
use function next;
use function reset;

class CustomTraversable implements Iterator
{
    /** @var array */
    private $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function current(): mixed
    {
        return current($this->data);
    }

    public function next(): void
    {
        next($this->data);
    }

    /** @return int|string */
    public function key(): mixed
    {
        return key($this->data);
    }

    public function valid(): bool
    {
        return $this->key() !== null;
    }

    public function rewind(): void
    {
        reset($this->data);
    }
}
