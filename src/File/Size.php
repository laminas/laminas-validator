<?php

declare(strict_types=1);

namespace Laminas\Validator\File;

use Laminas\Validator\AbstractValidator;
use Laminas\Validator\Exception\InvalidArgumentException;

use function is_string;

/**
 * Validator for the maximum size of a file
 *
 * @psalm-type OptionsArgument = array{
 *     min?: string|numeric|null,
 *     max?: string|numeric|null,
 *     useByteString?: bool,
 * }
 */
final class Size extends AbstractValidator
{
    public const TOO_BIG   = 'fileSizeTooBig';
    public const TOO_SMALL = 'fileSizeTooSmall';
    public const NOT_FOUND = 'fileSizeNotFound';

    /** @var array<string, string> */
    protected array $messageTemplates = [
        self::TOO_BIG   => "Maximum allowed size for file is '%max%' but '%size%' detected",
        self::TOO_SMALL => "Minimum expected size for file is '%min%' but '%size%' detected",
        self::NOT_FOUND => 'File is not readable or does not exist',
    ];

    /** @var array<string, string|array> */
    protected array $messageVariables = [
        'min'  => 'minString',
        'max'  => 'maxString',
        'size' => 'size',
    ];

    /**
     * Detected size
     */
    protected string $size = '';
    protected readonly string $minString;
    protected readonly string $maxString;

    protected readonly int|null $min;
    protected readonly int|null $max;
    private readonly bool $useByteString;

    /**
     * Sets validator options
     *
     * $options accepts the following keys:
     * 'min': Minimum file size
     * 'max': Maximum file size
     * 'useByteString': Use bytestring or real size for messages
     *
     * @param OptionsArgument $options
     */
    public function __construct(array $options = [])
    {
        $min                 = $options['min'] ?? null;
        $max                 = $options['max'] ?? null;
        $this->useByteString = $options['useByteString'] ?? true;

        if ($min === null && $max === null) {
            throw new InvalidArgumentException('One of `min` or `max` options are required');
        }

        if (is_string($min)) {
            $min = FileInformation::siUnitToBytes($min);
        }

        if (is_string($max)) {
            $max = FileInformation::siUnitToBytes($max);
        }

        $this->min = $min !== null ? (int) $min : null;
        $this->max = $max !== null ? (int) $max : null;

        if ($this->min !== null && $this->max !== null && $this->min > $this->max) {
            throw new InvalidArgumentException('The `min` option cannot exceed the `max` option');
        }

        unset(
            $options['min'],
            $options['max'],
            $options['useByteString'],
        );

        $this->minString = $this->min !== null && $this->useByteString
            ? FileInformation::bytesToSiUnit($this->min)
            : (string) $this->min;
        $this->maxString = $this->max !== null && $this->useByteString
            ? FileInformation::bytesToSiUnit($this->max)
            : (string) $this->max;

        parent::__construct($options);
    }

    /**
     * Returns true if and only if the file size of $value is at least min and
     * not bigger than max (when max is not null).
     */
    public function isValid(mixed $value): bool
    {
        if (! FileInformation::isPossibleFile($value)) {
            $this->error(self::NOT_FOUND);

            return false;
        }

        $file = FileInformation::factory($value);

        $this->value = $file->clientFileName ?? $file->baseName;

        if (! $file->readable) {
            $this->error(self::NOT_FOUND);
            return false;
        }

        $size       = $file->size();
        $this->size = $this->useByteString
            ? $file->sizeAsSiUnit()
            : (string) $size;

        if ($this->min !== null && $size < $this->min) {
            $this->error(self::TOO_SMALL);

            return false;
        }

        if ($this->max !== null && $size > $this->max) {
            $this->error(self::TOO_BIG);

            return false;
        }

        return true;
    }
}
