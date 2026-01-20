<?php

declare(strict_types=1);

namespace Laminas\Validator;

use Laminas\Translator\TranslatorInterface;
use Laminas\Validator\Exception\InvalidArgumentException;

use function array_key_exists;
use function gettype;
use function is_int;
use function is_iterable;
use function is_string;
use function iterator_to_array;
use function preg_match;

/**
 * @psalm-type OptionsArgument = array{
 *     key: array-key,
 *     messages?: array<string, string>,
 *     translator?: TranslatorInterface|null,
 *     translatorTextDomain?: string|null,
 *     translatorEnabled?: bool,
 *     valueObscured?: bool,
 * }
 */
final class KeyExists extends AbstractValidator
{
    public const ERR_NOT_ITERABLE  = 'errorNotIterable';
    public const ERR_KEY_NOT_FOUND = 'errorKeyNotFound';

    protected readonly string|int $key;
    /** @psalm-suppress PossiblyUnusedProperty This property is used in message interpolation. */
    protected string $type = 'null';

    /** @var array<string, string|array<string, string>> */
    protected array $messageVariables = [
        'key'  => 'key',
        'type' => 'type',
    ];

    /**
     * Validation failure message template definitions
     *
     * @var array<string, string>
     */
    protected array $messageTemplates = [
        self::ERR_NOT_ITERABLE  => 'Invalid type given. Expected iterable but %type% received.',
        self::ERR_KEY_NOT_FOUND => 'The key %key% was not found in the iterable.',
    ];

    /** @param OptionsArgument $options */
    public function __construct(array $options)
    {
        $key = $options['key'] ?? null;
        if (! is_string($key) && ! is_int($key)) {
            throw new InvalidArgumentException('The `key` option is required to be a string or integer');
        }

        $this->key = preg_match('/^[0-9]+$/', (string) $key) ? (int) $key : $key;
        parent::__construct($options);
    }

    public function isValid(mixed $value): bool
    {
        $this->value = $value;
        $this->type  = gettype($value);

        if (! is_iterable($value)) {
            $this->error(self::ERR_NOT_ITERABLE);

            return false;
        }

        if (! array_key_exists($this->key, iterator_to_array($value))) {
            $this->error(self::ERR_KEY_NOT_FOUND);

            return false;
        }

        return true;
    }
}
