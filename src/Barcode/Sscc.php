<?php

declare(strict_types=1);

namespace Laminas\Validator\Barcode;

use Override;

use function is_numeric;
use function strlen;

final class Sscc implements AdapterInterface
{
    #[Override]
    public function hasValidLength(string $value): bool
    {
        return strlen($value) === 18;
    }

    #[Override]
    public function hasValidCharacters(string $value): bool
    {
        return is_numeric($value);
    }

    #[Override]
    public function hasValidChecksum(string $value): bool
    {
        return Util::gtin($value);
    }

    #[Override]
    public function getLength(): int
    {
        return 18;
    }
}
