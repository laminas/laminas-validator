<?php

declare(strict_types=1);

namespace Laminas\Validator;

use function array_key_exists;
use function is_numeric;

/**
 * @psalm-type OptionsArgument = array{
 *     max: numeric,
 *     inclusive?: bool,
 *     ...<string, mixed>,
 * }
 */
final class LessThan extends AbstractValidator
{
    public const NOT_LESS           = 'notLessThan';
    public const NOT_LESS_INCLUSIVE = 'notLessThanInclusive';
    public const NOT_NUMERIC        = 'notNumeric';
    /**
     * Validation failure message template definitions
     *
     * @var array<string, string>
     */
    protected array $messageTemplates = [
        self::NOT_LESS           => "The input is not less than '%max%'",
        self::NOT_LESS_INCLUSIVE => "The input is not less or equal than '%max%'",
        self::NOT_NUMERIC        => "Expected a numeric value",
    ];

    /**
     * Additional variables available for validation failure messages
     *
     * @var array<string, string>
     */
    protected array $messageVariables = [
        'max' => 'max',
    ];

    /**
     * Maximum value
     */
    protected float $max;

    /**
     * Whether to do inclusive comparisons, allowing equivalence to max
     *
     * If false, then strict comparisons are done, and the value may equal
     * the max option
     */
    private bool $inclusive;

    /**
     * Sets validator options
     *
     * @param OptionsArgument $options
     * @throws Exception\InvalidArgumentException
     */
    public function __construct(array $options)
    {
        if (! array_key_exists('max', $options)) {
            throw new Exception\InvalidArgumentException("Missing option 'max'");
        }

        $this->max       = (float) $options['max'];
        $this->inclusive = $options['inclusive'] ?? false;

        parent::__construct($options);
    }

    /**
     * Returns true if and only if $value is less than max option, inclusively
     * when the inclusive option is true
     */
    public function isValid(mixed $value): bool
    {
        $this->setValue($value);

        if (! is_numeric($value)) {
            $this->error(self::NOT_NUMERIC);

            return false;
        }

        if ($this->inclusive) {
            if ($value > $this->max) {
                $this->error(self::NOT_LESS_INCLUSIVE);
                return false;
            }
        } else {
            if ($value >= $this->max) {
                $this->error(self::NOT_LESS);
                return false;
            }
        }

        return true;
    }
}
