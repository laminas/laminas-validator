<?php

declare(strict_types=1);

namespace Laminas\Validator\Barcode;

use Override;

use function strlen;

/** @psalm-import-type AllowedLength from AdapterInterface */
final class Ean8 implements AdapterInterface
{
    #[Override]
    public function hasValidLength(string $value): bool
    {
        return strlen($value) === 7 || strlen($value) === 8;
    }

    #[Override]
    public function hasValidCharacters(string $value): bool
    {
        return Util::stringMatchesAlphabet($value, '01234567890');
    }

    #[Override]
    public function hasValidChecksum(string $value): bool
    {
        if (strlen($value) === 7) {
            return true;
        }

        return Util::gtin($value);
    }

    #[Override]
    public function getLength(): int|string|array|null
    {
        return [7, 8];
    }
}
