<?php

declare(strict_types=1);

namespace Laminas\Validator\File;

use Laminas\Validator\Exception\RuntimeException;
use Psr\Http\Message\UploadedFileInterface;

use function assert;
use function basename;
use function ctype_digit;
use function file_exists;
use function filesize;
use function finfo_open;
use function is_array;
use function is_int;
use function is_numeric;
use function is_readable;
use function is_string;
use function round;
use function strtoupper;
use function substr;
use function trim;

use const FILEINFO_MIME_TYPE;
use const PHP_INT_MAX;

/** @internal */
final class FileInformation
{
    public readonly string $baseName;
    public readonly bool $readable;
    private string|null $mediaType;

    private function __construct(
        public readonly string $path,
        public readonly ?string $clientFileName,
        public readonly ?string $clientMediaType,
        private int|null $size,
    ) {
        $this->readable  = is_readable($this->path);
        $this->baseName  = basename($this->path);
        $this->mediaType = null;
    }

    public function size(): int
    {
        if ($this->size === null) {
            $this->size = filesize($this->path);
        }

        return $this->size;
    }

    public function sizeAsSiUnit(): string
    {
        return self::bytesToSiUnit($this->size());
    }

    public function detectMimeType(): string
    {
        if ($this->mediaType === null) {
            $fileInfo = finfo_open(FILEINFO_MIME_TYPE);

            $mime = $fileInfo->file($this->path);
            assert(is_string($mime));

            $this->mediaType = $mime;
        }

        return $this->mediaType;
    }

    public static function factory(mixed $value): self
    {
        if (! self::isPossibleFile($value)) {
            throw new RuntimeException('Cannot detect any file information');
        }

        /** @psalm-var array<array-key, mixed>|string|UploadedFileInterface $value */

        if ($value instanceof UploadedFileInterface) {
            $path = $value->getStream()->getMetadata('uri');
            assert(is_string($path));

            return new self(
                $path,
                $value->getClientFilename(),
                $value->getClientMediaType(),
                $value->getSize(),
            );
        }

        if (is_string($value)) {
            return new self($value, null, null, null);
        }

        return self::fromSapiArray($value);
    }

    private static function fromSapiArray(array $value): self
    {
        $clientName = $value['name'] ?? null;
        $clientType = $value['type'] ?? null;
        $path       = $value['tmp_name'] ?? null;
        $size       = $value['size'] ?? null;

        assert(is_string($path));
        assert(is_string($clientName));
        assert(is_string($clientType));
        assert(is_int($size) || $size === null);

        return new self($path, $clientName, $clientType, $size);
    }

    public static function isPossibleFile(mixed $value): bool
    {
        if ($value instanceof UploadedFileInterface) {
            return true;
        }

        if (
            is_array($value)
            && isset($value['tmp_name'])
            && is_string($value['tmp_name'])
            && $value['tmp_name'] !== ''
            && file_exists($value['tmp_name'])
        ) {
            return true;
        }

        if (is_string($value) && $value !== '' && file_exists($value)) {
            return true;
        }

        return false;
    }

    /**
     * Format filesize in bytes to an SI Unit
     */
    public static function bytesToSiUnit(int $size): string
    {
        $sizes = ['B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
        for ($i = 0; $size >= 1024 && $i < 9; $i++) {
            $size /= 1024;
        }

        $suffix = $sizes[$i] ?? null;

        assert(is_string($suffix));

        return round($size, 2) . $suffix;
    }

    /**
     * Convert an SI unit to bytes
     */
    public static function siUnitToBytes(string $size): int
    {
        if (ctype_digit($size)) {
            return (int) $size;
        }

        $type = trim(substr($size, -2, 1));

        $value = substr($size, 0, -1);
        if (! is_numeric($value)) {
            $value = trim(substr($value, 0, -1));
        }

        assert(is_numeric($value));

        switch (strtoupper($type)) {
            case 'Y':
                //$value *= 1024 ** 8;
                $value = PHP_INT_MAX;
                break;
            case 'Z':
                //$value *= 1024 ** 7;
                $value = PHP_INT_MAX;
                break;
            case 'E':
                if ($value > 7) {
                    $value = PHP_INT_MAX;
                    break;
                }
                $value *= 1024 ** 6;
                break;
            case 'P':
                $value *= 1024 ** 5;
                break;
            case 'T':
                $value *= 1024 ** 4;
                break;
            case 'G':
                $value *= 1024 ** 3;
                break;
            case 'M':
                $value *= 1024 ** 2;
                break;
            case 'K':
                $value *= 1024;
                break;
            default:
                break;
        }

        return (int) $value;
    }
}
