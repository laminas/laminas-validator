<?php

declare(strict_types=1);

namespace LaminasTest\Validator;

use DateTime;
use DateTimeImmutable;
use Laminas\Validator;
use PHPUnit\Framework\TestCase;
use stdClass;

use function array_keys;
use function date_get_last_errors;
use function var_export;

/**
 * @group      Laminas_Validator
 */
class DateTest extends TestCase
{
    /** @var Validator\Date */
    protected $validator;

    /**
     * Creates a new Laminas\Validator\Date object for each test method
     */
    protected function setUp(): void
    {
        $this->validator = new Validator\Date();
    }

    public function testSetFormatIgnoresNull(): void
    {
        $this->validator->setFormat(null);
        $this->assertEquals(Validator\Date::FORMAT_DEFAULT, $this->validator->getFormat());
    }

    /**
     * @return array[]
     * @psalm-return array<array{
     *     0: string|numeric|DateTime|object|array,
     *     1: null|string,
     *     2: bool,
     *     3: bool
     * }>
     */
    public function datesDataProvider(): array
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
            ['2007-01-01T12:02:55Z',    DateTime::ISO8601, true,     false],
            ['2007-01-01T12:02:55+0000', DateTime::ISO8601, true,    true],
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
        ];
    }

    /**
     * Ensures that the validator follows expected behavior
     *
     * @dataProvider datesDataProvider
     * @param string|numeric|DateTime|object|array $input
     */
    public function testBasic($input, ?string $format, bool $result, bool $resultStrict): void
    {
        $this->validator->setFormat($format);
        /** @psalm-suppress ArgumentTypeCoercion */
        $this->assertEquals($result, $this->validator->isValid($input));
    }

    /**
     * @dataProvider datesDataProvider
     * @param string|numeric|DateTime|object|array $input
     */
    public function testBasicStrictMode($input, ?string $format, bool $result, bool $resultStrict): void
    {
        $this->validator->setStrict(true);
        $this->validator->setFormat($format);
        /** @psalm-suppress ArgumentTypeCoercion */
        $this->assertSame($resultStrict, $this->validator->isValid($input));
    }

    public function testDateTimeImmutable(): void
    {
        $this->assertTrue($this->validator->isValid(new DateTimeImmutable()));
    }

    /**
     * Ensures that getMessages() returns expected default value
     */
    public function testGetMessages(): void
    {
        $this->assertEquals([], $this->validator->getMessages());
    }

    /**
     * Ensures that the validator can handle different manual dateformats
     *
     * @group Laminas-2003
     */
    public function testUseManualFormat(): void
    {
        $this->assertTrue(
            $this->validator->setFormat('d.m.Y')->isValid('10.01.2008'),
            var_export(date_get_last_errors(), true)
        );
        $this->assertEquals('d.m.Y', $this->validator->getFormat());

        $this->assertTrue($this->validator->setFormat('m Y')->isValid('01 2010'));
        $this->assertFalse($this->validator->setFormat('d/m/Y')->isValid('2008/10/22'));
        $this->assertTrue($this->validator->setFormat('d/m/Y')->isValid('22/10/08'));
        $this->assertFalse($this->validator->setFormat('d/m/Y')->isValid('22/10'));
        $this->assertTrue($this->validator->setFormat('s')->isValid('00'));
        $this->assertFalse($this->validator->setFormat('s')->isValid('0'));
    }

    public function testEqualsMessageTemplates(): void
    {
        $this->assertSame(
            [
                Validator\Date::INVALID,
                Validator\Date::INVALID_DATE,
                Validator\Date::FALSEFORMAT,
            ],
            array_keys($this->validator->getMessageTemplates())
        );
        $this->assertEquals($this->validator->getOption('messageTemplates'), $this->validator->getMessageTemplates());
    }

    public function testEqualsMessageVariables(): void
    {
        $messageVariables = [
            'format' => 'format',
        ];
        $this->assertSame($messageVariables, $this->validator->getOption('messageVariables'));
        $this->assertEquals(array_keys($messageVariables), $this->validator->getMessageVariables());
    }

    public function testConstructorWithFormatParameter(): void
    {
        $format    = 'd/m/Y';
        $validator = new Validator\Date($format);

        $this->assertEquals($format, $validator->getFormat());
    }
}
