<?php

declare(strict_types=1);

namespace Laminas\Validator;

use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use stdClass;
use Traversable;

use function array_shift;
use function func_get_args;
use function gettype;
use function implode;
use function is_array;
use function iterator_to_array;

/**
 * Validates that a given value is a DateTime instance or can be converted into one.
 */
class Date extends AbstractValidator
{
    /**#@+
     * Validity constants
     */
    public const INVALID      = 'dateInvalid';
    public const INVALID_DATE = 'dateInvalidDate';
    public const FALSEFORMAT  = 'dateFalseFormat';
    /**#@-*/

    /**
     * Default format constant
     */
    public const FORMAT_DEFAULT = 'Y-m-d';

    /**
     * Validation failure message template definitions
     *
     * @var string[]
     */
    protected array $messageTemplates = [
        self::INVALID      => 'Invalid type given. String, integer, array or DateTime expected',
        self::INVALID_DATE => 'The input does not appear to be a valid date',
        self::FALSEFORMAT  => "The input does not fit the date format '%format%'",
    ];

    /** @var string[] */
    protected array $messageVariables = [
        'format' => 'format',
    ];

    protected string $format = self::FORMAT_DEFAULT;

    protected bool $strict = false;

    /**
     * Sets validator options
     *
     * @param string|array|Traversable $options OPTIONAL
     */
    public function __construct($options = [])
    {
        if ($options instanceof Traversable) {
            $options = iterator_to_array($options);
        } elseif (! is_array($options)) {
            $options        = func_get_args();
            $temp           = [];
            $temp['format'] = array_shift($options);
            $options        = $temp;
        }

        parent::__construct($options);
    }

    /**
     * Returns the format option
     */
    public function getFormat(): string
    {
        return $this->format;
    }

    /**
     * Sets the format option
     *
     * Format cannot be null.  It will always default to 'Y-m-d', even
     * if null is provided.
     *
     * @param string|null $format
     * @return $this provides a fluent interface
     * @todo   validate the format
     */
    public function setFormat($format = self::FORMAT_DEFAULT)
    {
        $this->format = empty($format) ? self::FORMAT_DEFAULT : $format;
        return $this;
    }

    public function setStrict(bool $strict): self
    {
        $this->strict = $strict;
        return $this;
    }

    public function isStrict(): bool
    {
        return $this->strict;
    }

    /**
     * Returns true if $value is a DateTimeInterface instance or can be converted into one.
     *
     * @param string|int|float|array|stdClass|DateTimeInterface|null $value
     */
    public function isValid($value): bool
    {
        $this->setValue($value);

        $date = $this->convertToDateTime($value);
        if (! $date) {
            $this->error(self::INVALID_DATE);
            return false;
        }

        if ($this->isStrict() && $date->format($this->getFormat()) !== $value) {
            $this->error(self::FALSEFORMAT);
            return false;
        }

        return true;
    }

    /**
     * Attempts to convert an int, string, or array to a DateTime object
     *
     * @param string|int|float|array|stdClass|DateTimeInterface|null $param
     * @return false|DateTime
     */
    protected function convertToDateTime($param): bool|DateTime
    {
        if ($param instanceof DateTime) {
            return $param;
        }

        if ($param instanceof DateTimeImmutable) {
            return DateTime::createFromImmutable($param);
        }

        /**
         * @psalm-suppress PossiblyInvalidArgument
         */
        return match (gettype($param)) {
            'string' => $this->convertString($param),
            'integer', 'float' => $this->convertNumber($param),
            'array' => $this->convertArray($param),
            default => $this->addInvalidTypeError(),
        };
    }

    /**
     * @return false
     */
    private function addInvalidTypeError(): bool
    {
        $this->error(self::INVALID);
        return false;
    }

    /**
     * Attempts to convert an number (float or integer) into a DateTime object
     */
    protected function convertNumber(int|float $value): false|DateTime
    {
        return DateTime::createFromFormat('U', (string) $value);
    }

    /**
     * Attempts to convert a string into a DateTime object
     */
    protected function convertString(string $value): false|DateTime
    {
        $date = DateTime::createFromFormat($this->format, $value);

        // Invalid dates can show up as warnings (ie. "2007-02-99")
        // and still return a DateTime object.
        $errors = DateTime::getLastErrors();
        if ($errors === false) {
            return $date;
        }

        if ($errors['warning_count'] > 0) {
            $this->error(self::FALSEFORMAT);
            return false;
        }

        return $date;
    }

    /**
     * Implodes the array into a string and proxies to {@link convertString()}.
     *
     * @todo enhance the implosion
     */
    protected function convertArray(array $value): false|DateTime
    {
        return $this->convertString(implode('-', $value));
    }
}
