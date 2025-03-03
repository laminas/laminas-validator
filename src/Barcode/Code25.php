<?php

declare(strict_types=1);

namespace Laminas\Validator\Barcode;

use Override;

use function is_numeric;

final class Code25 implements AdapterInterface
{
    #[Override]
    public function hasValidLength(string $value): bool
    {
        return true;
    }

    #[Override]
    public function hasValidCharacters(string $value): bool
    {
        return is_numeric($value);
    }

    #[Override]
    public function hasValidChecksum(string $value): bool
    {
        return Util::code25($value);
    }

    #[Override]
    public function getLength(): int
    {
        return -1;
    }
}
