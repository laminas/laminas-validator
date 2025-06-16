<?php

declare(strict_types=1);

namespace LaminasTest\Validator;

use Laminas\Validator\Exception\InvalidArgumentException;
use Laminas\Validator\Timezone;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use stdClass;

final class TimezoneTest extends TestCase
{
    /**
     * Test locations by type is string
     */
    #[DataProvider('locationProvider')]
    public function testLocations(mixed $value, bool $valid, string|null $expectError): void
    {
        $validator = new Timezone(['type' => Timezone::LOCATION]);
        self::assertSame($valid, $validator->isValid($value));

        if ($expectError !== null) {
            self::assertArrayHasKey($expectError, $validator->getMessages());
        }
    }

    /**
     * Provides location values
     *
     * @psalm-return array<array-key, array{0: mixed, 1: bool, 2: string|null}>
     */
    public static function locationProvider(): array
    {
        return [
            ['America/Anguilla', true, null],
            ['Antarctica/Palmer', true, null],
            ['Asia/Dubai', true, null],
            ['Atlantic/Cape_Verde', true, null],
            ['Australia/Broken_Hill', true, null],
            ['America/Sao_Paulo', true, null],
            ['America/Toronto', true, null],
            ['Pacific/Easter', true, null],
            ['Europe/Copenhagen', true, null],
            ['Indian/Maldives', true, null],
            ['cest', false, Timezone::INVALID_TIMEZONE_LOCATION], // abbreviation of Anadyr Summer Time
            ['Asia/London', false, Timezone::INVALID_TIMEZONE_LOCATION], // wrong location
            ['', false, Timezone::INVALID], // empty string
            [null, false, Timezone::INVALID], // null value
            [99, false, Timezone::INVALID], // non-string
        ];
    }

    /**
     * Test abbreviations
     */
    #[DataProvider('abbreviationProvider')]
    public function testAbbreviations(mixed $value, bool $valid, string|null $expectError): void
    {
        $validator = new Timezone(['type' => Timezone::ABBREVIATION]);
        self::assertSame($valid, $validator->isValid($value));

        if ($expectError !== null) {
            self::assertArrayHasKey($expectError, $validator->getMessages());
        }
    }

    /**
     * Provides abbreviation values
     *
     * @return array<array-key, array{0: mixed, 1: bool, 2: string|null}>
     */
    public static function abbreviationProvider(): array
    {
        return [
            ['cest', true, null], // Central European Summer Time
            ['hkt', true, null], // Hong Kong Time
            ['nzdt', true, null], // New Zealand Daylight Time
            ['sast', true, null], // South Africa Standard Time
            ['SAST', true, null], // SA standard time in uppercase
            ['America/Toronto', false, Timezone::INVALID_TIMEZONE_ABBREVIATION], // location
            ['xyz', false, Timezone::INVALID_TIMEZONE_ABBREVIATION], // wrong abbreviation
            ['', false, Timezone::INVALID], // empty string
            [null, false, Timezone::INVALID], // null value
            [99, false, Timezone::INVALID], // non-string
        ];
    }

    #[DataProvider('locationAndAbbreviationProvider')]
    public function testLocationsAndAbbreviations(mixed $value, bool $valid, string|null $expectError): void
    {
        $validator = new Timezone(['type' => Timezone::ALL]);
        self::assertSame($valid, $validator->isValid($value));

        if ($expectError !== null) {
            self::assertArrayHasKey($expectError, $validator->getMessages());
        }
    }

    /**
     * Provides location and abbreviation values
     *
     * @psalm-return array<array-key, array{0: mixed, 1: bool, 2: null|string}>
     */
    public static function locationAndAbbreviationProvider(): array
    {
        return [
            ['America/Anguilla', true, null],
            ['Antarctica/Palmer', true, null],
            ['Asia/Dubai', true, null],
            ['Atlantic/Cape_Verde', true, null],
            ['Australia/Broken_Hill', true, null],
            ['hkt', true, null], // Hong Kong Time
            ['nzdt', true, null], // New Zealand Daylight Time
            ['sast', true, null], // South Africa Standard Time
            ['xyz', false, Timezone::INVALID], // wrong abbreviation
            ['Asia/London', false, Timezone::INVALID], // wrong location
            ['', false, Timezone::INVALID], // empty string
            [null, false, Timezone::INVALID], // null value
            [99, false, Timezone::INVALID], // non-string
        ];
    }

    #[DataProvider('getInvalidTypes')]
    public function testRejectsInvalidIntType(mixed $invalidType): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The type option must be an int-mask of the type constants');

        /** @psalm-suppress MixedArgumentTypeCoercion - Intentionally invalid arguments */
        new Timezone(['type' => $invalidType]);
    }

    /** @return list<array{0: mixed}> */
    public static function getInvalidTypes(): array
    {
        return [
            [new stdClass()],
            [[]],
            [0],
            [4],
            ['foo'],
        ];
    }
}
