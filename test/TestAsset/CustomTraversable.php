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

    /** @return mixed */
    public function current()
    {
        return current($this->data);
    }

    /** @return void */
    public function next()
    {
        next($this->data);
    }

    /** @return int|string */
    public function key()
    {
        return key($this->data);
    }

    /** @return bool */
    public function valid()
    {
        return $this->key() !== null;
    }

    /** @return void */
    public function rewind()
    {
        reset($this->data);
    }
}
