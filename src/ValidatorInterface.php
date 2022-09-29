<?php

declare(strict_types=1);

namespace Laminas\Validator;

/**
 * @psalm-type ValidatorSpecification = array{
 *     name: string|class-string<ValidatorInterface>,
 *     priority?: int,
 *     break_chain_on_failure?: bool,
 *     options?: array<string, mixed>,
 * }
 */
interface ValidatorInterface
{
    /**
     * Returns a validation result that can be used to determine the validity of $value
     *
     * @throws Exception\RuntimeException If validation of $value is impossible.
     */
    public function validate(mixed $value, ?array $context = null): ValidationResult;

    /**
     * Returns true if and only if $value meets the validation requirements
     *
     * @param array<string, mixed> $context
     * @throws Exception\RuntimeException If validation of $value is impossible.
     */
    public function isValid(mixed $value, ?array $context = null): bool;
}
