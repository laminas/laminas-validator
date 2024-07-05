<?php

declare(strict_types=1);

namespace Laminas\Validator\File;

use Laminas\Validator\Exception\RuntimeException;
use Psr\Http\Message\UploadedFileInterface;

use function assert;
use function basename;
use function file_exists;
use function finfo_open;
use function is_array;
use function is_readable;
use function is_string;

use const FILEINFO_MIME_TYPE;

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
    ) {
        $this->readable  = is_readable($this->path);
        $this->baseName  = basename($this->path);
        $this->mediaType = null;
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
            );
        }

        if (is_string($value)) {
            return new self($value, null, null);
        }

        return self::fromSapiArray($value);
    }

    private static function fromSapiArray(array $value): self
    {
        $clientName = $value['name'] ?? null;
        $clientType = $value['type'] ?? null;
        $path       = $value['tmp_name'] ?? null;

        assert(is_string($path));
        assert(is_string($clientName));
        assert(is_string($clientType));

        return new self($path, $clientName, $clientType);
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
}
