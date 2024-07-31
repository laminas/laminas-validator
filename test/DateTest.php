<?php

declare(strict_types=1);

namespace LaminasTest\Validator;

use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use Laminas\Validator\Date;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use stdClass;

final class DateTest extends TestCase
{
    public function testNullFormatUsesDefault(): void
    {
        $validator = new Date([
            'format' => null,
        ]);

        self::assertTrue($validator->isValid('2020-01-01'));
    }

    /**
     * @return array<array{
     *     0: mixed,
     *     1: null|string,
     *     2: bool,
     *     3: bool
     * }>
     */
    public static function datesDataProvider(): array
    {
        return [
            // date                     format             isValid   isValid Strict
            ['2007-01-01',              null,              true,     true],
            ['2007-02-28',              null,              true,     true],
            ['2007-02-29',              null,              false,    false],
            ['2008-02-29',              null,              true,     true],
            ['2007-02-30',              null,              false,    false],
            ['2007-02-99',              null,              false,    false],
            ['2007-02-99',              'Y-m-d',           false,    false],
            ['9999-99-99',              null,              false,    false],
            ['9999-99-99',              'Y-m-d',           false,    false],
            ['Jan 1 2007',              null,              false,    false],
            ['Jan 1 2007',              'M j Y',           true,     true],
            ['asdasda',                 null,              false,    false],
            ['sdgsdg',                  null,              false,    false],
            ['2007-01-01something',     null,              false,    false],
            ['something2007-01-01',     null,              false,    false],
            ['10.01.2008',              'd.m.Y',           true,     true],
            ['01 2010',                 'm Y',             true,     true],
            ['2008/10/22',              'd/m/Y',           false,    false],
            ['22/10/08',                'd/m/y',           true,     true],
            ['22/10',                   'd/m/Y',           false,    false],
            // time
            ['2007-01-01T12:02:55Z',    DateTimeInterface::ATOM, true,     false],
            ['2007-01-01T12:02:55+0000', DateTimeInterface::ISO8601, true,    true],
            ['12:02:55',                'H:i:s',           true,     true],
            ['25:02:55',                'H:i:s',           false,    false],
            // int
            [0,                         null,              true,     false],
            [6,                         'd',               true,     false],
            ['6',                       'd',               true,     false],
            ['06',                      'd',               true,     true],
            [123,                       null,              true,     false],
            [1_340_677_235,                null,              true,     false],
            [1_340_677_235,                'U',               true,     false],
            ['1340677235',              'U',               true,     true],
            // 32bit version of php will convert this to double
            [999_999_999_999,              null,              true,     false],
            // double
            [12.12,                     null,              false,    false],
            // array
            [['2012', '06', '25'], null, true, false],
            // 0012-06-25 is a valid date, if you want 2012, use 'y' instead of 'Y'
            [['12', '06', '25'], null, true, false],
            [['2012', '06', '33'], null, false, false],
            [[1 => 1], null, false, false],
            // DateTime
            [new DateTime(),            null,              true,     false],
            // invalid obj
            [new stdClass(),            null,              false,    false],
            // Empty Values
            [[], null, false, false],
            ['',                        null,              false,    false],
            [null,                      null,              false,    false],
        ];
    }

    /**
     * Ensures that the validator follows expected behavior
     */
    #[DataProvider('datesDataProvider')]
    public function testBasic(mixed $input, ?string $format, bool $result): void
    {
        $validator = new Date([
            'format' => $format,
        ]);

        self::assertSame($result, $validator->isValid($input));
    }

    #[DataProvider('datesDataProvider')]
    public function testBasicStrictMode(mixed $input, ?string $format, bool $result, bool $resultStrict): void
    {
        $validator = new Date([
            'format' => $format,
            'strict' => true,
        ]);

        self::assertSame($resultStrict, $validator->isValid($input));
    }

    public function testDateTimeInstanceIsValid(): void
    {
        self::assertTrue((new Date())->isValid(new DateTimeImmutable()));
        self::assertTrue((new Date())->isValid(new DateTime()));
    }

    public static function manualFormatProvider(): array
    {
        return [
            ['d.m.Y', '10.01.2008', true],
            ['m Y', '01 2010', true],
            ['d/m/Y', '2008/10/22', false],
            ['d/m/Y', '22/10/08', true],
            ['d/m/Y', '22/10', false],
            ['s', '00', true],
            ['s', '0', false],
        ];
    }

    #[DataProvider('manualFormatProvider')]
    public function testUseManualFormat(string $format, mixed $input, bool $expect): void
    {
        $validator = new Date(['format' => $format]);
        self::assertSame($expect, $validator->isValid($input));
    }
}
