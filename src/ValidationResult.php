<?php

declare(strict_types=1);

namespace Laminas\Validator;

/**
 * @template T of mixed
 * @psalm-immutable
 */
interface ValidationResult
{
    public function isValid(): bool;

    /** @return T */
    public function value(): mixed;
}
