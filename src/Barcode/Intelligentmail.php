<?php

declare(strict_types=1);

namespace Laminas\Validator\Barcode;

use Override;

use function in_array;
use function is_numeric;
use function strlen;

final class Intelligentmail implements AdapterInterface
{
    private const LENGTH = [20, 25, 29, 31];

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
        return true;
    }

    #[Override]
    public function getLength(): array
    {
        return self::LENGTH;
    }
}
