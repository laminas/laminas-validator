<?php

declare(strict_types=1);

namespace Laminas\Validator\Barcode;

use Override;

use function strlen;

/** @psalm-import-type AllowedLength from AdapterInterface */
final class Ean5 implements AdapterInterface
{
    #[Override]
    public function hasValidLength(string $value): bool
    {
        return strlen($value) === 5;
    }

    #[Override]
    public function hasValidCharacters(string $value): bool
    {
        return Util::stringMatchesAlphabet($value, '0123456789');
    }

    #[Override]
    public function hasValidChecksum(string $value): bool
    {
        return true;
    }

    #[Override]
    public function getLength(): int|string|array|null
    {
        return 5;
    }
}
