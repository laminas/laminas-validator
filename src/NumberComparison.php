<?php

declare(strict_types=1);

namespace Laminas\Validator;

use Laminas\Validator\Exception\InvalidArgumentException;

use function is_numeric;

/**
 * @psalm-type OptionsArgument = array{
 *     min?: numeric|null,
 *     max?: numeric|null,
 *     inclusiveMin?: bool,
 *     inclusiveMax?: bool,
 *     ...<string, mixed>
 * }
 * @psalm-type Options = array{
 *     min: numeric|null,
 *     max: numeric|null,
 *     inclusiveMin: bool,
 *     inclusiveMax: bool,
 * }
 */
final class NumberComparison extends AbstractValidator
{
    public const ERROR_NOT_NUMERIC           = 'notNumeric';
    public const ERROR_NOT_GREATER_INCLUSIVE = 'notGreaterInclusive';
    public const ERROR_NOT_GREATER           = 'notGreater';
    public const ERROR_NOT_LESS_INCLUSIVE    = 'notLessInclusive';
    public const ERROR_NOT_LESS              = 'notLess';

    /** @var array<string, string> */
    protected array $messageTemplates = [
        self::ERROR_NOT_NUMERIC           => 'Expected a numeric value',
        self::ERROR_NOT_GREATER_INCLUSIVE => 'Values must be greater than or equal to %min%. Received "%value%"',
        self::ERROR_NOT_GREATER           => 'Values must be greater than %min%. Received "%value%',
        self::ERROR_NOT_LESS_INCLUSIVE    => 'Values must be less than or equal to %max%. Received "%value%"',
        self::ERROR_NOT_LESS              => 'Values must be less than %max%. Received "%value%"',
    ];

    /** @var array<string, array<string, string>> */
    protected array $messageVariables = [
        'min' => ['options' => 'min'],
        'max' => ['options' => 'max'],
    ];

    /** @var Options */
    protected array $options = [
        'min'          => null,
        'max'          => null,
        'inclusiveMin' => true,
        'inclusiveMax' => true,
    ];

    /** @param OptionsArgument $options */
    public function __construct(array $options = [])
    {
        parent::__construct($options);

        $min = $options['min'] ?? null;
        $max = $options['max'] ?? null;

        if (! is_numeric($min) && ! is_numeric($max)) {
            throw new InvalidArgumentException(
                'A numeric option value for either min, max or both must be provided',
            );
        }

        if ($min !== null && $max !== null && $min > $max) {
            throw new InvalidArgumentException(
                'The minimum constraint cannot be greater than the maximum constraint',
            );
        }

        $this->options['min']          = $min;
        $this->options['max']          = $max;
        $this->options['inclusiveMin'] = $options['inclusiveMin'] ?? true;
        $this->options['inclusiveMax'] = $options['inclusiveMax'] ?? true;
    }

    public function isValid(mixed $value): bool
    {
        if (! is_numeric($value)) {
            $this->error(self::ERROR_NOT_NUMERIC);

            return false;
        }

        $this->setValue($value);

        $min          = $this->options['min'];
        $max          = $this->options['max'];
        $inclusiveMin = $this->options['inclusiveMin'];
        $inclusiveMax = $this->options['inclusiveMax'];

        if ($min !== null && $inclusiveMin && $value < $min) {
            $this->error(self::ERROR_NOT_GREATER_INCLUSIVE);

            return false;
        }

        if ($min !== null && ! $inclusiveMin && $value <= $min) {
            $this->error(self::ERROR_NOT_GREATER);

            return false;
        }

        if ($max !== null && $inclusiveMax && $value > $max) {
            $this->error(self::ERROR_NOT_LESS_INCLUSIVE);

            return false;
        }

        if ($max !== null && ! $inclusiveMax && $value >= $max) {
            $this->error(self::ERROR_NOT_LESS);

            return false;
        }

        return true;
    }
}
