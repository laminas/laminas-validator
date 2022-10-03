<?php // phpcs:disable SlevomatCodingStandard.Namespaces.UnusedUses.UnusedUse

namespace LaminasTest\Validator\TestAsset;

use Iterator;
use ReturnTypeWillChange;

use function current;
use function key;
use function next;
use function reset;

final class CustomTraversable implements Iterator
{
    private array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /** @return mixed */
    #[ReturnTypeWillChange]
    public function current()
    {
        return current($this->data);
    }

    public function next(): void
    {
        next($this->data);
    }

    /** @return int|string */
    #[ReturnTypeWillChange]
    public function key()
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
