<?php

declare(strict_types=1);

namespace LaminasTest\Validator\File;

use Laminas\Validator\File\Bytes;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use const PHP_INT_MAX;

/** @psalm-suppress InternalClass, InternalMethod, InternalProperty */
class BytesTest extends TestCase
{
    /** @return list<array{0: int, 1: string}> */
    public static function bytesToSiUnitDataProvider(): array
    {
        return [
            [10, '10B'],
            [1536, '1.5kB'],
            [2_621_440, '2.5MB'],
            [1_073_741_824, '1GB'],
            [6_442_450_944, '6GB'],
            [6_597_069_766_656, '6TB'],
            [6_755_399_441_055_744, '6PB'],
            [2_147_483_647, '2GB'], // PHP_INT_MAX on 32-bit
        ];
    }

    #[DataProvider('bytesToSiUnitDataProvider')]
    public function testBytesToSiUnit(int $input, string $expect): void
    {
        self::assertSame($expect, Bytes::fromInteger($input)->toSiUnit());
    }

    public static function siUnitToBytesProvider(): array
    {
        return [
            [10, '10b'],
            [1536, '1.5kB'],
            [2_621_440, '2.5MB'],
            [1_073_741_824, '1GB'],
            [6_442_450_944, '6GB'],
            [6_597_069_766_656, '6TB'],
            [10, '10 b'],
            [1536, '1.5 kB'],
            [1536, '1.5 kb'],
            [2_621_440, '2.5 MB'],
            [1_073_741_824, '1 GB'],
            [6_442_450_944, '6 GB'],
            [6_597_069_766_656, '6 TB'],
            [6_755_399_441_055_744, '6 PB'],
            [8_070_450_532_247_928_832, '7EB'],
            [PHP_INT_MAX, '8EB'],
            [PHP_INT_MAX, '1ZB'],
            [PHP_INT_MAX, '10YB'],
        ];
    }

    #[DataProvider('siUnitToBytesProvider')]
    public function testSiUnitToBytes(int $expect, string $input): void
    {
        self::assertSame($expect, Bytes::fromSiUnit($input)->bytes);
    }

    public function testThatFromSiUnitAcceptsNumericString(): void
    {
        $bytes = Bytes::fromSiUnit('1024');

        self::assertSame(1024, $bytes->bytes);
        self::assertSame('1kB', $bytes->toSiUnit());
    }
}
