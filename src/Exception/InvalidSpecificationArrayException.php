<?php

declare(strict_types=1);

namespace Laminas\Validator\Exception;

use InvalidArgumentException;

use function get_debug_type;
use function sprintf;

final class InvalidSpecificationArrayException extends InvalidArgumentException implements ExceptionInterface
{
    public static function becauseItemsMustBeArraysOrValidators(mixed $spec): self
    {
        return new self(sprintf(
            'Each item in a specification array must be either an array or a validator instance. Received %s',
            get_debug_type($spec),
        ));
    }

    public static function becauseTheNameIsARequiredKey(mixed $name): self
    {
        return new self(sprintf(
            'The `name` key must be defined and should be a string representing the FQCN of a validator or an '
            . 'alias. Received %s',
            get_debug_type($name),
        ));
    }

    public static function becauseOptionsMustBeAnArray(mixed $options): self
    {
        return new self(sprintf(
            'When given, the `options` key must be an array. Received %s',
            get_debug_type($options),
        ));
    }

    public static function becauseBreakChainMustBeBoolean(mixed $breakChain): self
    {
        return new self(sprintf(
            'When given, the `break_chain_on_failure` key must be boolean. Received %s',
            get_debug_type($breakChain),
        ));
    }

    public static function becausePriorityMustBeAnInteger(mixed $priority): self
    {
        return new self(sprintf(
            'When given, the `priority` key must be an integer. Received %s',
            get_debug_type($priority),
        ));
    }
}
