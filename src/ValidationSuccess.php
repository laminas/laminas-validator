<?php

declare(strict_types=1);

namespace Laminas\Validator;

/**
 * @template T of mixed
 * @psalm-immutable
 */
final class ValidationSuccess implements ValidationResult
{
    /** @param T $value */
    private function __construct(private mixed $value)
    {
    }

    /**
     * @param TValue $value
     * @return self<TValue>
     * @template TValue of mixed
     */
    public static function new(mixed $value): self
    {
        return new self($value);
    }

    public function isValid(): bool
    {
        return true;
    }

    /** @return T */
    public function value(): mixed
    {
        return $this->value;
    }
}
