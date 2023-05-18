<?php

declare(strict_types=1);

namespace LaminasTest\Validator;

use Laminas\Validator\GpsPoint;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class GPSPointTest extends TestCase
{
    private GpsPoint $validator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = new GpsPoint();
    }

    #[DataProvider('basicDataProvider')]
    public function testBasic(string $gpsPoint): void
    {
        self::assertTrue($this->validator->isValid($gpsPoint));
    }

    /** @psalm-return array<array-key, array{0: string, 1: bool}> */
    public static function boundariesProvider(): array
    {
        return [
            ['181.8897,-77.0089', false],
            ['38.8897,-181.0089', false],
            ['-181.8897,-77.0089', false],
            ['38.8897,181.0089', false],
        ];
    }

    #[DataProvider('boundariesProvider')]
    public function testBoundariesAreRespected(string $value, bool $expected): void
    {
        self::assertSame($expected, $this->validator->isValid($value));
    }

    #[DataProvider('errorMessageTestValues')]
    public function testErrorsSetOnOccur(string $value, string $messageKey, string $messageValue): void
    {
        self::assertFalse($this->validator->isValid($value));

        $messages = $this->validator->getMessages();

        self::assertArrayHasKey($messageKey, $messages);
        self::assertStringContainsString($messageValue, $messages[$messageKey]);
    }

    /**
     * @psalm-return array<array-key, array{0: string}>
     */
    public static function basicDataProvider(): array
    {
        return [
            ['38° 53\' 23" N, 77° 00\' 32" W'],
            ['15° 22\' 20.137" S, 35° 35\' 14.686" E'],
            ['65° 4\' 36.434" N,-22.728867530822754'],
            ['38.8897°, -77.0089°'],
            ['38.8897,-77.0089'],
        ];
    }

    /**
     * @psalm-return array<array-key, array{0: string, 1: string, 2: string}>
     */
    public static function errorMessageTestValues(): array
    {
        return [
            ['63 47 24.691 N, 18 2 54.363 W', GpsPoint::OUT_OF_BOUNDS, '63 47 24.691 N'],
            ['° \' " N,° \' " E', GpsPoint::CONVERT_ERROR, '° \' " N'],
            ['° \' " N', GpsPoint::INCOMPLETE_COORDINATE, '° \' " N'],
        ];
    }
}
