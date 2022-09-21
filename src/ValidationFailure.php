<?php

declare(strict_types=1);

namespace Laminas\Validator;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use Laminas\Validator\Exception\InvalidArgumentException;
use Laminas\Validator\Translator\TranslatorInterface;
use Traversable;

use function array_key_exists;
use function array_merge;
use function array_replace;
use function assert;
use function count;
use function is_array;
use function is_bool;
use function is_float;
use function is_int;
use function is_object;
use function is_resource;
use function is_string;
use function method_exists;
use function sprintf;
use function str_repeat;
use function str_replace;
use function strlen;

/**
 * @template T of mixed
 * @implements ValidationResult<T>
 * @implements IteratorAggregate<string, non-empty-string>
 * @psalm-immutable
 */
final class ValidationFailure implements ValidationResult, IteratorAggregate, Countable
{
    /**
     * @param array<string, non-empty-string>  $templates     Message templates that will be interpolated with the given
     *                                                        value and other message variables.
     * @param array<string, mixed>             $variables     Arbitrary variables to interpolate into error messages.
     * @param T                                $value         The input value that was validated.
     * @param list<non-empty-string>           $errors        The message template keys that describe the failure.
     * @param bool                             $valueObscured Whether the value should be obscured in error messages.
     * @param TranslatorInterface|null         $translator    An optional translator with which to translate error
     *                                                        messages.
     * @param non-empty-string                 $textDomain    Translator text domain to use for translations.
     */
    private function __construct(
        private array $templates,
        private array $variables,
        private mixed $value,
        private array $errors,
        private bool $valueObscured = false,
        private ?TranslatorInterface $translator = null,
        private string $textDomain = 'default',
    ) {
    }

    /**
     * @param array<string, non-empty-string>  $templates     Message templates that will be interpolated with the given
     *                                                        value and other message variables.
     * @param array<string, mixed>             $variables     Arbitrary variables to interpolate into error messages.
     * @param TValue                           $value         The input value that was validated.
     * @param list<non-empty-string>           $errors        The message template keys that describe the failure.
     * @param bool                             $valueObscured Whether the value should be obscured in error messages.
     * @param TranslatorInterface|null         $translator    An optional translator with which to translate error
     *                                                        messages.
     * @param non-empty-string                 $textDomain    Translator text domain to use for translations.
     * @return self<TValue>
     * @template TValue of mixed
     */
    public static function new(
        array $templates,
        array $variables,
        mixed $value,
        array $errors,
        bool $valueObscured = false,
        ?TranslatorInterface $translator = null,
        string $textDomain = 'default',
    ): self {
        return new self($templates, $variables, $value, $errors, $valueObscured, $translator, $textDomain);
    }

    public function isValid(): bool
    {
        return false;
    }

    /** @return T */
    public function value(): mixed
    {
        return $this->value;
    }

    /**
     * Return a clone with an updated value - i.e. the value that was validated.
     *
     * @param T $value
     * @return self<T>
     */
    public function withValue(mixed $value): self
    {
        $result        = clone $this;
        $result->value = $value;

        return $result;
    }

    /**
     * Return a clone with the additional error listed as a reason for validation failure
     *
     * @param non-empty-string $key
     * @throws InvalidArgumentException If $key is not present in the list of message templates.
     */
    public function withError(string $key): self
    {
        if (! array_key_exists($key, $this->templates)) {
            throw new InvalidArgumentException(sprintf(
                'An error message template named "%s" does not exist',
                $key
            ));
        }

        $result           = clone $this;
        $result->errors[] = $key;

        return $result;
    }

    public function withVariable(string $name, mixed $value): self
    {
        $result                   = clone $this;
        $result->variables[$name] = $value;

        return $result;
    }

    /** @return non-empty-array<non-empty-string, non-empty-string> */
    public function getMessages(): array
    {
        $messages = [];
        foreach ($this->errors as $key) {
            assert(array_key_exists($key, $this->templates));
            $message = $this->templates[$key];
            if ($this->translator) {
                /** @psalm-suppress ImpureMethodCall $message */
                $message = $this->translator->translate($message, $this->textDomain);
            }

            $message = $this->interpolateVariable('value', $this->getValue(), $message);
            /** @psalm-suppress MixedAssignment */
            foreach ($this->variables as $name => $variable) {
                $message = $this->interpolateVariable($name, $variable, $message);
            }

            assert($message !== '');

            $messages[$key] = $message;
        }

        return $messages;
    }

    public function merge(self $other): self
    {
        $result            = clone $this;
        $result->errors    = array_merge($result->errors, $other->errors);
        $result->templates = array_replace($result->templates, $other->templates);
        $result->variables = array_replace($result->variables, $other->variables);

        return $result;
    }

    public function getValue(): string
    {
        $value = self::stringify($this->value);
        if ($this->valueObscured) {
            $value = str_repeat('*', strlen($value));
        }

        return $value;
    }

    private function interpolateVariable(string $name, mixed $value, string $intoMessage): string
    {
        return str_replace(
            '%' . $name . '%',
            self::stringify($value),
            $intoMessage
        );
    }

    /** @psalm-pure */
    private static function stringify(mixed $value): string
    {
        if (is_float($value) || is_int($value) || is_string($value)) {
            return (string) $value;
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if ($value === null) {
            return 'null';
        }

        if (is_array($value)) {
            return 'array';
        }

        if (is_resource($value)) {
            return 'resource';
        }

        assert(is_object($value));

        return self::stringifyObject($value);
    }

    /** @psalm-pure */
    private static function stringifyObject(object $value): string
    {
        if (method_exists($value, 'toString')) {
            return (string) $value->toString();
        }

        if (method_exists($value, '__toString')) {
            return (string) $value;
        }

        return $value::class;
    }

    /** @return Traversable<string, non-empty-string> */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->getMessages());
    }

    public function count(): int
    {
        return count($this->errors);
    }
}
