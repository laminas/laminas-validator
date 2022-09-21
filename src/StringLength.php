<?php

declare(strict_types=1);

namespace Laminas\Validator;

use Laminas\Stdlib\StringUtils;
use Laminas\Stdlib\StringWrapper\StringWrapperInterface as StringWrapper;
use Laminas\Stdlib\StringWrapper\StringWrapperInterface;
use Traversable;

use function array_shift;
use function func_get_args;
use function is_array;
use function is_string;
use function max;

/**
 * @psalm-type OptionsArray array{
 *     min: int,
 *     max: int|null,
 *     encoding: string,
 * }
 */
class StringLength implements ValidatorInterface
{
    public const INVALID   = 'stringLengthInvalid';
    public const TOO_SHORT = 'stringLengthTooShort';
    public const TOO_LONG  = 'stringLengthTooLong';

    private const TEMPLATES = [
        self::INVALID   => 'Invalid type given. String expected',
        self::TOO_SHORT => 'The input is less than %min% characters long',
        self::TOO_LONG  => 'The input is more than %max% characters long',
    ];

    /** @var OptionsArray */
    private array $options = [
        'min'      => 0, // Minimum length
        'max'      => null, // Maximum length, null if there is no length limitation
        'encoding' => 'UTF-8', // Encoding to use
    ];

    /** @var null|StringWrapperInterface */
    protected $stringWrapper;

    /**
     * Sets validator options
     *
     * @param int|array|Traversable $options
     */
    public function __construct($options = [])
    {
        if (! is_array($options)) {
            $options     = func_get_args();
            $temp['min'] = array_shift($options);
            if (! empty($options)) {
                $temp['max'] = array_shift($options);
            }

            if (! empty($options)) {
                $temp['encoding'] = array_shift($options);
            }

            $options = $temp;
        }
    }

    /**
     * Returns the min option
     *
     * @return int
     */
    public function getMin()
    {
        return $this->options['min'];
    }

    /**
     * Sets the min option
     *
     * @param  int $min
     * @throws Exception\InvalidArgumentException
     * @return $this Provides a fluent interface
     */
    public function setMin($min)
    {
        if (null !== $this->getMax() && $min > $this->getMax()) {
            throw new Exception\InvalidArgumentException(
                "The minimum must be less than or equal to the maximum length, but {$min} > {$this->getMax()}"
            );
        }

        $this->options['min'] = max(0, (int) $min);
        return $this;
    }

    /**
     * Returns the max option
     *
     * @return int|null
     */
    public function getMax()
    {
        return $this->options['max'];
    }

    /**
     * Sets the max option
     *
     * @param  int|null $max
     * @throws Exception\InvalidArgumentException
     * @return $this Provides a fluent interface
     */
    public function setMax($max)
    {
        if (null === $max) {
            $this->options['max'] = null;
        } elseif ($max < $this->getMin()) {
            throw new Exception\InvalidArgumentException(
                "The maximum must be greater than or equal to the minimum length, but {$max} < {$this->getMin()}"
            );
        } else {
            $this->options['max'] = (int) $max;
        }

        return $this;
    }

    /**
     * Get the string wrapper to detect the string length
     *
     * @return StringWrapper
     */
    public function getStringWrapper(): StringWrapper
    {
        if (! $this->stringWrapper) {
            $this->stringWrapper = StringUtils::getWrapper($this->getEncoding());
        }
        return $this->stringWrapper;
    }

    /**
     * Set the string wrapper to detect the string length
     *
     * @return void
     */
    public function setStringWrapper(StringWrapper $stringWrapper)
    {
        $stringWrapper->setEncoding($this->getEncoding());
        $this->stringWrapper = $stringWrapper;
    }

    /**
     * Returns the actual encoding
     *
     * @return string
     */
    public function getEncoding()
    {
        return $this->options['encoding'];
    }

    /**
     * Sets a new encoding to use
     *
     * @param string $encoding
     * @return $this
     * @throws Exception\InvalidArgumentException
     */
    public function setEncoding($encoding)
    {
        $this->stringWrapper       = StringUtils::getWrapper($encoding);
        $this->options['encoding'] = $encoding;
        return $this;
    }

    /**
     * Returns the length option
     *
     * @return int
     */
    private function getLength()
    {
        return $this->options['length'];
    }

    /**
     * Sets the length option
     *
     * @param  int $length
     * @return $this Provides a fluent interface
     */
    private function setLength($length)
    {
        $this->options['length'] = (int) $length;
        return $this;
    }

    public function isValid(mixed $value, ?array $context = null): bool
    {
        return $this->validate($value, $context)->isValid();
    }

    /**
     * Returns true if and only if the string length of $value is at least the min option and
     * no greater than the max option (when the max option is not null).
     *
     * @return ValidationFailure<mixed>|ValidationSuccess<string>
     */
    public function validate(mixed $value, ?array $context = null): ValidationResult
    {
        $result = ValidationFailure::new(
            self::TEMPLATES,
            [
                'min'    => $this->getMin(),
                'max'    => $this->getMax(),
                'length' => $this->getLength(),
            ],
            $value,
            [],
            false,
            null, // $this->translator
            'default' // $this->textDomain
        );

        if (! is_string($value)) {
            return $result->withError(self::INVALID);
        }

        $length = $this->getStringWrapper()->strlen($value);
        $result = $result->withVariable('length', $length);

        if ($length < $this->getMin()) {
            return $result->withError(self::TOO_SHORT);
        }

        if (null !== $this->getMax() && $this->getMax() < $length) {
            return $result->withError(self::TOO_LONG);
        }

        return ValidationSuccess::new($value);
    }
}
