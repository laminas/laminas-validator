<?php

declare(strict_types=1);

namespace Laminas\Validator\Barcode;

use Override;

use function in_array;
use function is_numeric;
use function strlen;

final class Upce implements AdapterInterface
{
    private const LENGTH = [6, 7, 8];

    /**
     * Overrides parent checkLength
     */
    #[Override]
    public function hasValidLength(string $value): bool
    {
        return in_array(strlen($value), self::LENGTH, true);
    }

    #[Override]
    public function hasValidCharacters(string $value): bool
    {
        return is_numeric($value);
    }

    #[Override]
    public function hasValidChecksum(string $value): bool
    {
        if (strlen($value) === 8) {
            return Util::gtin($value);
        }

        return true;
    }

    #[Override]
    public function getLength(): array
    {
        return self::LENGTH;
    }
}
