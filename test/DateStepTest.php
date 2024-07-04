<?php

declare(strict_types=1);

namespace LaminasTest\Validator;

use DateInterval;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use Laminas\Validator\DateStep;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

use function array_keys;
use function date;

final class DateStepTest extends TestCase
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
    public static function stepTestsDataProvider(): array
    {
        return [
            //    interval format            baseValue               value                  isValid
            ['PT1S', DateTimeInterface::ISO8601, '1970-01-01T00:00:00Z', '1970-01-01T00:00:00Z', true],
            ['PT1S', DateTimeInterface::ISO8601, '1970-01-01T00:00:00Z', '1970-01-03T00:00:00Z', true],
            ['PT1S', DateTimeInterface::ISO8601, '1970-01-01T00:00:00Z', '1970-01-01T00:00:02Z', true],
            ['PT2S', DateTimeInterface::ISO8601, '1970-01-01T00:00:00Z', '1970-01-01T00:00:01Z', false],
            ['PT2S', DateTimeInterface::ISO8601, '1970-01-01T00:00:00Z', '1970-01-01T00:00:16Z', true],
            ['PT2S', DateTimeInterface::ISO8601, '1970-01-01T00:00:00Z', '1970-01-03T00:00:00Z', true],
            // minutes
            ['PT1M', DateTimeInterface::ISO8601, '1970-01-01T00:00:00Z', '1970-03-01T00:00:00Z', true],
            ['PT1M', DateTimeInterface::ISO8601, '1970-01-01T00:00:00Z', '1970-03-01T00:00:30Z', false],
            ['PT1M', DateTimeInterface::ISO8601, '1970-01-01T00:00:00Z', '1970-01-01T00:02:00Z', true],
            ['PT2M', DateTimeInterface::ISO8601, '1970-01-01T00:00:00Z', '1970-01-01T00:01:00Z', false],
            ['PT2M', DateTimeInterface::ISO8601, '1970-01-01T00:00:00Z', '1970-01-01T00:16:00Z', true],
            ['PT2M', DateTimeInterface::ISO8601, '1970-01-01T00:00:00Z', '1970-03-01T00:00:00Z', true],
            ['PT1M', 'H:i:s',           '00:00:00',             '12:34:00',             true],
            ['PT2M', 'H:i:s',           '00:00:00',             '12:34:00',             true],
            ['PT2M', 'H:i:s',           '00:00:00',             '12:35:00',             false],
            // hours
            ['PT1H', DateTimeInterface::ISO8601, '1970-01-01T00:00:00Z', '1970-03-01T00:00:00Z', true],
            ['PT1H', DateTimeInterface::ISO8601, '1970-01-01T00:00:00Z', '1970-03-01T00:00:30Z', false],
            ['PT1H', DateTimeInterface::ISO8601, '1970-01-01T00:00:00Z', '1970-01-01T02:00:00Z', true],
            ['PT2H', DateTimeInterface::ISO8601, '1970-01-01T00:00:00Z', '1970-01-01T01:00:00Z', false],
            ['PT2H', DateTimeInterface::ISO8601, '1970-01-01T00:00:00Z', '1970-01-01T16:00:00Z', true],
            ['PT2H', DateTimeInterface::ISO8601, '1970-01-01T00:00:00Z', '1970-03-01T00:00:00Z', true],
            // days
            ['P1D',  DateTimeInterface::ISO8601, '1970-01-01T00:00:00Z', '1973-01-01T00:00:00Z', true],
            ['P1D',  DateTimeInterface::ISO8601, '1970-01-01T00:00:00Z', '1973-01-01T00:00:30Z', false],
            ['P1D',  DateTimeInterface::ISO8601, '1970-01-01T00:00:00Z', '2014-08-12T00:00:00Z', true],
            ['P2D',  DateTimeInterface::ISO8601, '1970-01-01T00:00:00Z', '1970-01-02T00:00:00Z', false],
            ['P2D',  DateTimeInterface::ISO8601, '1970-01-01T00:00:00Z', '1970-01-15T00:00:00Z', true],
            ['P2D',  DateTimeInterface::ISO8601, '1971-01-01T00:00:00Z', '1973-01-01T00:00:00Z', false],
            ['P2D',  DateTimeInterface::ISO8601, '2000-01-01T00:00:00Z', '2001-01-01T00:00:00Z', true], // leap year
            // weeks
            ['P1W',  DateTimeInterface::ISO8601, '1970-01-01T00:00:00Z', '1970-01-29T00:00:00Z', true],
            // months
            ['P1M',  DateTimeInterface::ISO8601, '1970-01-01T00:00:00Z', '1973-01-01T00:00:00Z', true],
            ['P1M',  DateTimeInterface::ISO8601, '1970-01-01T00:00:00Z', '1973-01-01T00:00:30Z', false],
            ['P2M',  DateTimeInterface::ISO8601, '1970-01-01T00:00:00Z', '1970-02-01T00:00:00Z', false],
            ['P2M',  DateTimeInterface::ISO8601, '1970-01-01T00:00:00Z', '1971-05-01T00:00:00Z', true],
            ['P1M',  'Y-m',             '1970-01',              '1970-10',              true],
            ['P2M',  '!Y-m',            '1970-01',              '1970-11',              true],
            ['P2M',  'Y-m',             '1970-01',              '1970-10',              false],
            // years
            ['P1Y',  DateTimeInterface::ISO8601, '1970-01-01T00:00:00Z', '1973-01-01T00:00:00Z', true],
            ['P1Y',  DateTimeInterface::ISO8601, '1970-01-01T00:00:00Z', '1973-01-01T00:00:30Z', false],
            ['P2Y',  DateTimeInterface::ISO8601, '1970-01-01T00:00:00Z', '1971-01-01T00:00:00Z', false],
            ['P2Y',  DateTimeInterface::ISO8601, '1970-01-01T00:00:00Z', '1976-01-01T00:00:00Z', true],
            // complex
            ['P2M2DT12H', DateTimeInterface::ISO8601, '1970-01-01T00:00:00Z', '1970-03-03T12:00:00Z', true],
            ['P2M2DT12M', DateTimeInterface::ISO8601, '1970-01-01T00:00:00Z', '1970-03-03T12:00:00Z', false],
            // long interval
            ['PT1M20S', DateTimeInterface::ISO8601, '1970-01-01T00:00:00Z', '2020-09-13T12:26:40Z', true], // 20,000,000 steps
            ['PT1M20S', DateTimeInterface::ISO8601, '1970-01-01T00:00:00Z', '2020-09-13T12:26:41Z', false],
            ['P2W',  'Y-\WW',           '1970-W01',             '1973-W16',             true],
            ['P2W',  'Y-\WW',           '1970-W01',             '1973-W17',             false],
        ];
    }

    #[DataProvider('stepTestsDataProvider')]
    public function testDateStepValidation(
        string $interval,
        string $format,
        string $baseValue,
        string $value,
        bool $isValid
    ): void {
        $validator = new DateStep([
            'format'    => $format,
            'baseValue' => $baseValue,
            'step'      => new DateInterval($interval),
        ]);

        self::assertSame($isValid, $validator->isValid($value));
    }

    /**
     * The exact base and test value matter here.
     * By having a different date and a step of seconds the fallbackIncrementalIterationLogic will run.
     */
    public function testWithDateTimeType(): void
    {
        $validator = new DateStep([
            'format'    => DateTimeInterface::ISO8601,
            'baseValue' => new DateTime('1970-01-01T00:00:00Z'),
            'step'      => new DateInterval('PT2S'),
        ]);

        self::assertTrue($validator->isValid(new DateTime('1970-01-03T00:00:02Z')));
    }

    /**
     * The exact base and test value matter here.
     * By having a different date and a step of seconds the fallbackIncrementalIterationLogic will run.
     */
    public function testWithDateTimeImmutableType(): void
    {
        $validator = new DateStep([
            'format'    => DateTimeInterface::ISO8601,
            'baseValue' => new DateTimeImmutable('1970-01-01T00:00:00Z'),
            'step'      => new DateInterval('PT2S'),
        ]);

        self::assertTrue($validator->isValid(new DateTimeImmutable('1970-01-03T00:00:02Z')));
    }

    public function testGetMessagesReturnsDefaultValue(): void
    {
        $validator = new DateStep();

        self::assertSame([], $validator->getMessages());
    }

    public function testEqualsMessageTemplates(): void
    {
        $validator = new DateStep([]);

        self::assertSame(
            [
                DateStep::INVALID,
                DateStep::INVALID_DATE,
                DateStep::FALSEFORMAT,
                DateStep::NOT_STEP,
            ],
            array_keys($validator->getMessageTemplates())
        );
        self::assertSame($validator->getOption('messageTemplates'), $validator->getMessageTemplates());
    }

    public function testStepError(): void
    {
        $validator = new DateStep([
            'format'    => 'Y-m-d',
            'baseValue' => '2012-01-23',
            'step'      => new DateInterval('P10D'),
        ]);

        self::assertFalse($validator->isValid('2012-02-23'));
    }

    /**
     * @return array[]
     * @psalm-return array<array{0: string}>
     */
    public static function moscowWinterTimeDataProvider(): array
    {
        // dates before during and after Moscow's wintertime
        return [
            ['26-03-1999'],
            ['26-03-2011'],
            ['27-03-2011'],
            ['26-03-2015'],
        ];
    }

    #[DataProvider('moscowWinterTimeDataProvider')]
    public function testMoscowWinterTime(string $dateToValidate): void
    {
        $validator = new DateStep([
            'format'    => 'd-m-Y',
            'baseValue' => date('d-m-Y', 0),
            'step'      => new DateInterval('P1D'),
        ]);

        self::assertTrue($validator->isValid($dateToValidate));
    }

    public function testCanSetBaseValue(): void
    {
        $validator = new DateStep([
            'baseValue' => '2013-01-23',
            'step'      => 'P2D',
        ]);

        self::assertFalse($validator->isValid('2013-01-24'));
        self::assertTrue($validator->isValid('2013-01-25'));
    }

    public function testCanProvideOptionsToConstructor(): void
    {
        $baseValue = '23-01-2012';
        $step      = new DateInterval('P2D');
        $format    = 'd-m-Y';

        $validator = new DateStep([
            'baseValue' => $baseValue,
            'step'      => $step,
            'format'    => $format,
        ]);

        self::assertFalse($validator->isValid('24-01-2012'));
        self::assertTrue($validator->isValid('25-01-2012'));
    }

    public function testConvertStringDoesNotRaiseErrorOnInvalidValue(): void
    {
        $validator = new DateStep([
            'format'    => 'Y-m-d',
            'baseValue' => '2012-01-23',
            'step'      => new DateInterval('P10D'),
        ]);

        $r = new ReflectionMethod($validator, 'convertString');

        $invalidValue = '20-20-20';

        // Verify that the value returns false for an invalid value
        self::assertFalse($r->invoke($validator, $invalidValue, false));

        // Verify that no message was set.
        self::assertSame([], $validator->getMessages());
    }
}
