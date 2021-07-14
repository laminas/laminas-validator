<?php

declare(strict_types=1);

namespace LaminasTest\Validator;

use DateInterval;
use DateTime;
use DateTimeImmutable;
use DateTimeZone;
use Laminas\Validator;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

use function array_keys;
use function date;

/**
 * @group      Laminas_Validator
 */
class DateStepTest extends TestCase
{
    /**
     * @return array[]
     * @psalm-return array<array{
     *     0: string,
     *     1: string,
     *     2: string,
     *     3: string,
     *     4: bool
     * }>
     */
    public function stepTestsDataProvider(): array
    {
        return [
            //    interval format            baseValue               value                  isValid
            ['PT1S', DateTime::ISO8601, '1970-01-01T00:00:00Z', '1970-01-01T00:00:00Z', true],
            ['PT1S', DateTime::ISO8601, '1970-01-01T00:00:00Z', '1970-01-03T00:00:00Z', true],
            ['PT1S', DateTime::ISO8601, '1970-01-01T00:00:00Z', '1970-01-01T00:00:02Z', true],
            ['PT2S', DateTime::ISO8601, '1970-01-01T00:00:00Z', '1970-01-01T00:00:01Z', false],
            ['PT2S', DateTime::ISO8601, '1970-01-01T00:00:00Z', '1970-01-01T00:00:16Z', true],
            ['PT2S', DateTime::ISO8601, '1970-01-01T00:00:00Z', '1970-01-03T00:00:00Z', true],
            // minutes
            ['PT1M', DateTime::ISO8601, '1970-01-01T00:00:00Z', '1970-03-01T00:00:00Z', true],
            ['PT1M', DateTime::ISO8601, '1970-01-01T00:00:00Z', '1970-03-01T00:00:30Z', false],
            ['PT1M', DateTime::ISO8601, '1970-01-01T00:00:00Z', '1970-01-01T00:02:00Z', true],
            ['PT2M', DateTime::ISO8601, '1970-01-01T00:00:00Z', '1970-01-01T00:01:00Z', false],
            ['PT2M', DateTime::ISO8601, '1970-01-01T00:00:00Z', '1970-01-01T00:16:00Z', true],
            ['PT2M', DateTime::ISO8601, '1970-01-01T00:00:00Z', '1970-03-01T00:00:00Z', true],
            ['PT1M', 'H:i:s',           '00:00:00',             '12:34:00',             true],
            ['PT2M', 'H:i:s',           '00:00:00',             '12:34:00',             true],
            ['PT2M', 'H:i:s',           '00:00:00',             '12:35:00',             false],
            // hours
            ['PT1H', DateTime::ISO8601, '1970-01-01T00:00:00Z', '1970-03-01T00:00:00Z', true],
            ['PT1H', DateTime::ISO8601, '1970-01-01T00:00:00Z', '1970-03-01T00:00:30Z', false],
            ['PT1H', DateTime::ISO8601, '1970-01-01T00:00:00Z', '1970-01-01T02:00:00Z', true],
            ['PT2H', DateTime::ISO8601, '1970-01-01T00:00:00Z', '1970-01-01T01:00:00Z', false],
            ['PT2H', DateTime::ISO8601, '1970-01-01T00:00:00Z', '1970-01-01T16:00:00Z', true],
            ['PT2H', DateTime::ISO8601, '1970-01-01T00:00:00Z', '1970-03-01T00:00:00Z', true],
            // days
            ['P1D',  DateTime::ISO8601, '1970-01-01T00:00:00Z', '1973-01-01T00:00:00Z', true],
            ['P1D',  DateTime::ISO8601, '1970-01-01T00:00:00Z', '1973-01-01T00:00:30Z', false],
            ['P1D',  DateTime::ISO8601, '1970-01-01T00:00:00Z', '2014-08-12T00:00:00Z', true],
            ['P2D',  DateTime::ISO8601, '1970-01-01T00:00:00Z', '1970-01-02T00:00:00Z', false],
            ['P2D',  DateTime::ISO8601, '1970-01-01T00:00:00Z', '1970-01-15T00:00:00Z', true],
            ['P2D',  DateTime::ISO8601, '1971-01-01T00:00:00Z', '1973-01-01T00:00:00Z', false],
            ['P2D',  DateTime::ISO8601, '2000-01-01T00:00:00Z', '2001-01-01T00:00:00Z', true], // leap year
            // weeks
            ['P1W',  DateTime::ISO8601, '1970-01-01T00:00:00Z', '1970-01-29T00:00:00Z', true],
            // months
            ['P1M',  DateTime::ISO8601, '1970-01-01T00:00:00Z', '1973-01-01T00:00:00Z', true],
            ['P1M',  DateTime::ISO8601, '1970-01-01T00:00:00Z', '1973-01-01T00:00:30Z', false],
            ['P2M',  DateTime::ISO8601, '1970-01-01T00:00:00Z', '1970-02-01T00:00:00Z', false],
            ['P2M',  DateTime::ISO8601, '1970-01-01T00:00:00Z', '1971-05-01T00:00:00Z', true],
            ['P1M',  'Y-m',             '1970-01',              '1970-10',              true],
            ['P2M',  '!Y-m',            '1970-01',              '1970-11',              true],
            ['P2M',  'Y-m',             '1970-01',              '1970-10',              false],
            // years
            ['P1Y',  DateTime::ISO8601, '1970-01-01T00:00:00Z', '1973-01-01T00:00:00Z', true],
            ['P1Y',  DateTime::ISO8601, '1970-01-01T00:00:00Z', '1973-01-01T00:00:30Z', false],
            ['P2Y',  DateTime::ISO8601, '1970-01-01T00:00:00Z', '1971-01-01T00:00:00Z', false],
            ['P2Y',  DateTime::ISO8601, '1970-01-01T00:00:00Z', '1976-01-01T00:00:00Z', true],
            // complex
            ['P2M2DT12H', DateTime::ISO8601, '1970-01-01T00:00:00Z', '1970-03-03T12:00:00Z', true],
            ['P2M2DT12M', DateTime::ISO8601, '1970-01-01T00:00:00Z', '1970-03-03T12:00:00Z', false],
            // long interval
            ['PT1M20S', DateTime::ISO8601, '1970-01-01T00:00:00Z', '2020-09-13T12:26:40Z', true], // 20,000,000 steps
            ['PT1M20S', DateTime::ISO8601, '1970-01-01T00:00:00Z', '2020-09-13T12:26:41Z', false],
            ['P2W',  'Y-\WW',           '1970-W01',             '1973-W16',             true],
            ['P2W',  'Y-\WW',           '1970-W01',             '1973-W17',             false],
        ];
    }

    /**
     * @dataProvider stepTestsDataProvider
     */
    public function testDateStepValidation(
        string $interval,
        string $format,
        string $baseValue,
        string $value,
        bool $isValid
    ): void {
        $validator = new Validator\DateStep([
            'format'    => $format,
            'baseValue' => $baseValue,
            'step'      => new DateInterval($interval),
        ]);

        $this->assertSame($isValid, $validator->isValid($value));
    }

    /**
     * The exact base and test value matter here.
     * By having a different date and a step of seconds the fallbackIncrementalIterationLogic will run.
     */
    public function testWithDateTimeType(): void
    {
        $validator = new Validator\DateStep([
            'format'    => DateTime::ISO8601,
            'baseValue' => new DateTime('1970-01-01T00:00:00Z'),
            'step'      => new DateInterval('PT2S'),
        ]);

        $this->assertTrue($validator->isValid(new DateTime('1970-01-03T00:00:02Z')));
    }

    /**
     * The exact base and test value matter here.
     * By having a different date and a step of seconds the fallbackIncrementalIterationLogic will run.
     */
    public function testWithDateTimeImmutableType(): void
    {
        $validator = new Validator\DateStep([
            'format'    => DateTime::ISO8601,
            'baseValue' => new DateTimeImmutable('1970-01-01T00:00:00Z'),
            'step'      => new DateInterval('PT2S'),
        ]);

        $this->assertTrue($validator->isValid(new DateTimeImmutable('1970-01-03T00:00:02Z')));
    }

    public function testGetMessagesReturnsDefaultValue(): void
    {
        $validator = new Validator\DateStep();
        $this->assertEquals([], $validator->getMessages());
    }

    public function testEqualsMessageTemplates(): void
    {
        $validator = new Validator\DateStep([]);
        $this->assertSame(
            [
                Validator\DateStep::INVALID,
                Validator\DateStep::INVALID_DATE,
                Validator\DateStep::FALSEFORMAT,
                Validator\DateStep::NOT_STEP,
            ],
            array_keys($validator->getMessageTemplates())
        );
        $this->assertEquals($validator->getOption('messageTemplates'), $validator->getMessageTemplates());
    }

    public function testStepError(): void
    {
        $validator = new Validator\DateStep([
            'format'    => 'Y-m-d',
            'baseValue' => '2012-01-23',
            'step'      => new DateInterval('P10D'),
        ]);

        $this->assertFalse($validator->isValid('2012-02-23'));
    }

    /**
     * @return array[]
     * @psalm-return array<array{0: string}>
     */
    public function moscowWinterTimeDataProvider(): array
    {
        // dates before during and after Moscow's wintertime
        return [
            ['26-03-1999'],
            ['26-03-2011'],
            ['27-03-2011'],
            ['26-03-2015'],
        ];
    }

    /**
     * @dataProvider moscowWinterTimeDataProvider
     */
    public function testMoscowWinterTime(string $dateToValidate): void
    {
        $validator = new Validator\DateStep([
            'format'    => 'd-m-Y',
            'baseValue' => date('d-m-Y', 0),
            'step'      => new DateInterval('P1D'),
            'timezone'  => new DateTimeZone('Europe/Moscow'),
        ]);

        $this->assertTrue($validator->isValid($dateToValidate));
    }

    public function testCanSetBaseValue(): void
    {
        $validator = new Validator\DateStep();

        $newBaseValue = '2013-01-23';
        $validator->setBaseValue($newBaseValue);

        $retrievedBaseValue = $validator->getBaseValue();

        $this->assertSame($newBaseValue, $retrievedBaseValue);
    }

    public function testCanRetrieveTimezone(): void
    {
        $validator = new Validator\DateStep();

        $newTimezone = new DateTimeZone('Europe/Vienna');
        $validator->setTimezone($newTimezone);

        $retrievedTimezone = $validator->getTimezone();

        $this->assertSame($newTimezone, $retrievedTimezone);
    }

    public function testCanProvideOptionsToConstructorAsDiscreteArguments(): void
    {
        $baseValue = '2012-01-23';
        $step      = new DateInterval('P1D');
        $format    = 'd-m-Y';
        $timezone  = new DateTimeZone('Europe/Vienna');

        $validator = new Validator\DateStep($baseValue, $step, $format, $timezone);

        $retrievedBaseValue = $validator->getBaseValue();
        $retrievedStep      = $validator->getStep();
        $retrievedFormat    = $validator->getFormat();
        $retrievedTimezone  = $validator->getTimezone();

        $this->assertSame($baseValue, $retrievedBaseValue);
        $this->assertSame($step, $retrievedStep);
        $this->assertSame($format, $retrievedFormat);
        $this->assertSame($timezone, $retrievedTimezone);
    }

    public function testConvertStringDoesNotRaiseErrorOnInvalidValue(): void
    {
        $validator = new Validator\DateStep([
            'format'    => 'Y-m-d',
            'baseValue' => '2012-01-23',
            'step'      => new DateInterval('P10D'),
        ]);

        $r = new ReflectionMethod($validator, 'convertString');
        $r->setAccessible(true);

        $invalidValue = '20-20-20';

        // Verify that the value returns false for an invalid value
        $this->assertFalse($r->invoke($validator, $invalidValue, false));

        // Verify that no message was set.
        $this->assertEquals([], $validator->getMessages());
    }
}
