<?php

declare(strict_types=1);

namespace Laminas\Validator;

use Closure;
use Exception;
use Laminas\Validator\Exception\InvalidArgumentException;

use function array_merge;
use function assert;
use function is_array;
use function is_bool;
use function is_callable;

/**
 * @psalm-type OptionsArgument = array{
 *     callback: callable(mixed...): bool,
 *     callbackOptions?: array<array-key, mixed>,
 *     bind?: bool,
 *     throwExceptions?: bool,
 * }&array<string, mixed>
 */
final class Callback extends AbstractValidator
{
    public const INVALID_CALLBACK = 'callbackInvalid';
    public const INVALID_VALUE    = 'callbackValue';

    /**
     * Validation failure message template definitions
     *
     * @var array<string, string>
     */
    protected array $messageTemplates = [
        self::INVALID_VALUE    => 'The input is not valid',
        self::INVALID_CALLBACK => 'An exception has been raised within the callback',
    ];

    /** @var Closure(mixed...): bool */
    private readonly Closure $callback;
    private readonly bool $throwExceptions;
    /** @var array<array-key, mixed> */
    private readonly array $callbackOptions;

    /** @param OptionsArgument|callable $options */
    public function __construct(array|callable $options)
    {
        if (! is_array($options)) {
            $options = ['callback' => $options];
        }

        /** @psalm-var OptionsArgument&array<string, mixed> $options */

        $callback        = $options['callback'] ?? null;
        $callbackOptions = $options['callbackOptions'] ?? [];
        $throw           = $options['throwExceptions'] ?? false;
        $bind            = $options['bind'] ?? false;

        if (! is_callable($callback)) {
            throw new InvalidArgumentException('A callable must be provided');
        }

        assert(is_bool($throw));
        assert(is_bool($bind));

        /** @psalm-var Closure(mixed...):bool $callback */
        $callback       = $bind
            ? $callback(...)->bindTo($this)
            : $callback(...);
        $this->callback = $callback;

        $this->throwExceptions = $throw;
        $this->callbackOptions = $callbackOptions;

        parent::__construct($options);
    }

    /**
     * Returns true if and only if the set callback returns true for the provided $value
     *
     * @param array<string, mixed> $context Additional context to provide to the callback
     */
    public function isValid(mixed $value, ?array $context = null): bool
    {
        $this->setValue($value);

        $hasContext = $context !== null && $context !== [];

        $args = [$value];
        if ($this->callbackOptions === [] && $hasContext) {
            $args[] = $context;
        }

        if ($this->callbackOptions !== [] && ! $hasContext) {
            $args = array_merge($args, $this->callbackOptions);
        }

        if ($this->callbackOptions !== [] && $hasContext) {
            $args[] = $context;
            $args   = array_merge($args, $this->callbackOptions);
        }

        try {
            $result = ($this->callback)(...$args);
        } catch (Exception $exception) {
            /**
             * Intentionally excluding catchable \Error as they are indicative of a bug and should not be suppressed
             */
            $this->error(self::INVALID_CALLBACK);

            if ($this->throwExceptions === true) {
                throw $exception;
            }

            return false;
        }

        if ($result !== true) {
            $this->error(self::INVALID_VALUE);

            return false;
        }

        return true;
    }
}
