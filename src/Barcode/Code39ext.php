<?php

declare(strict_types=1);

namespace Laminas\Validator\Barcode;

use Override;

final class Code39ext implements AdapterInterface
{
    #[Override]
    public function hasValidLength(string $value): bool
    {
        return true;
    }

    #[Override]
    public function hasValidCharacters(string $value): bool
    {
        return Util::isAscii128($value);
    }

    #[Override]
    public function hasValidChecksum(string $value): bool
    {
        return true;
    }

    #[Override]
    public function getLength(): int
    {
        return -1;
    }
}
