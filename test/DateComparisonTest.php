<?php

declare(strict_types=1);

namespace LaminasTest\Validator;

use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use Laminas\Validator\DateComparison;
use Laminas\Validator\Exception\InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use function assert;

class DateComparisonTest extends TestCase
{
    /**
     * phpcs:disable Generic.Files.LineLength
     *
     * @return list<array{
     *     0: string|DateTimeImmutable|null,
     *     1: string|DateTimeImmutable|null,
     *     2: bool,
     *     3: bool,
     *     4: null|string,
     *     5: bool,
     *     6: mixed,
     *     7: string|null,
     * }>
     */
    public static function basicDataProvider(): array
    {
        $jan1 = DateTimeImmutable::createFromFormat('!Y-m-d', '2020-01-01');
        $jan2 = DateTimeImmutable::createFromFormat('!Y-m-d', '2020-01-02');
        $feb1 = DateTimeImmutable::createFromFormat('!Y-m-d', '2020-02-01');
        $mar1 = DateTimeImmutable::createFromFormat('!Y-m-d', '2020-03-01');

        assert($jan1 !== false);
        assert($jan2 !== false);
        assert($feb1 !== false);
        assert($mar1 !== false);

        return [
            // Between with Dates
            ['2020-01-01', '2020-01-31', true, true, null, true, '2020-01-01', null],
            ['2020-01-01', '2020-01-31', false, false, null, false, '2020-01-01', DateComparison::ERROR_NOT_GREATER],
            ['2020-01-01', '2020-01-31', true, true, null, false, '2019-01-01', DateComparison::ERROR_NOT_GREATER_INCLUSIVE],
            ['2020-01-01', '2020-01-31', true, true, null, false, '2020-02-01', DateComparison::ERROR_NOT_LESS_INCLUSIVE],
            ['2020-01-01', '2020-01-31', false, false, null, false, '2020-02-01', DateComparison::ERROR_NOT_LESS],
            [$jan1, $mar1, true, true, null, true, $feb1, null],
            // Generally invalid
            ['2020-01-01', '2020-01-31', false, false, null, false, 'muppets', DateComparison::ERROR_INVALID_DATE],
            ['2020-01-01', '2020-01-31', false, false, 'jS F Y', false, 'foo', DateComparison::ERROR_INVALID_DATE],
            ['2020-01-01', '2020-01-31', false, false, 'jS F Y', false, 1, DateComparison::ERROR_INVALID_TYPE],
            ['2020-01-01', '2020-01-31', false, false, 'jS F Y', false, null, DateComparison::ERROR_INVALID_TYPE],
            ['2020-01-01', '2020-01-31', false, false, 'jS F Y', false, '', DateComparison::ERROR_INVALID_DATE],
            // Date formats are not lenient
            ['2020-01-01', '2020-01-31', true, true, null, false, '2020-1-1', DateComparison::ERROR_INVALID_DATE],
            ['2020-01-01', '2020-01-31', true, true, 'Y-n-j', true, '2020-1-1', null],
            ['2020-01-01', '2020-01-31', true, true, 'Y-n-j', true, '2020-01-01', null],
            ['2020-01-01', '2020-01-31', true, true, null, false, '2020-1-1T12:34:56', DateComparison::ERROR_INVALID_DATE],
            ['2020-01-01', '2020-01-31', true, true, null, false, '2020-0-01T12:34', DateComparison::ERROR_INVALID_DATE],
            // Between with custom input format
            ['2020-01-01', '2020-01-31', false, false, 'jS M Y', true, '5th Jan 2020', null],
            ['2020-01-01', '2020-01-31', false, false, 'jS M Y', true, '2020-01-05', null],
            ['2020-01-01', '2020-01-31', false, false, 'jS M Y', true, '2020-01-05T16:45:00', null],
            ['2020-01-01', '2020-01-31', true, true, 'jS M Y', true, $jan1, null],
            // Between with Date and Time
            ['2020-01-01T19:00:00', '2020-01-02T12:00:00', false, false, null, false, '2020-01-05T16:45:00', DateComparison::ERROR_NOT_LESS],
            ['2020-01-01T19:00:00', '2020-01-02T12:00:00', true, true, null, false, '2020-01-01', DateComparison::ERROR_NOT_GREATER_INCLUSIVE],
            ['2020-01-01T19:00:00', '2020-01-02T12:00:00', false, false, null, true, '2020-01-02T09:00:00', null],
            ['2020-01-01T19:00:00', '2020-01-02T12:00:00', false, false, null, true, $jan2, null],
            ['2020-01-01T19:00:00', '2020-01-02T12:00:00', false, false, null, false, '2020-01-02T12:00:00', DateComparison::ERROR_NOT_LESS],
            ['2020-01-01T19:00:00', '2020-01-02T12:00:00', true, true, null, false, '2020-01-02T12:00:01', DateComparison::ERROR_NOT_LESS_INCLUSIVE],
            ['2020-01-01T19:00:00', '2020-01-02T12:00:00', false, false, null, false, '2020-01-01T19:00:00', DateComparison::ERROR_NOT_GREATER],
            ['2020-01-01T19:00:00', '2020-01-02T12:00:00', true, true, null, false, '2020-01-01T18:59:59', DateComparison::ERROR_NOT_GREATER_INCLUSIVE],
            // Lower bound only
            ['2020-01-01', null, true, true, null, true, '2045-01-01', null],
            ['2020-01-01', null, true, true, null, false, '2010-01-01', DateComparison::ERROR_NOT_GREATER_INCLUSIVE],
            ['2020-01-01', null, false, false, null, false, '2010-01-01', DateComparison::ERROR_NOT_GREATER],
            // Upper bound only
            [null, '2020-01-01', true, true, null, true, '2010-01-01', null],
            [null, '2020-01-01', true, true, null, false, '2030-01-01', DateComparison::ERROR_NOT_LESS_INCLUSIVE],
            [null, '2020-01-01', false, false, null, false, '2030-01-01', DateComparison::ERROR_NOT_LESS],
        ];
    }

    #[DataProvider('basicDataProvider')]
    public function testBasicFunctionality(
        string|DateTimeInterface|null $min,
        string|DateTimeInterface|null $max,
        bool $inclusiveMin,
        bool $inclusiveMax,
        string|null $format,
        bool $expect,
        mixed $input,
        string|null $errorKey,
    ): void {
        $validator = new DateComparison([
            'min'          => $min,
            'max'          => $max,
            'inclusiveMin' => $inclusiveMin,
            'inclusiveMax' => $inclusiveMax,
            'inputFormat'  => $format,
        ]);

        self::assertSame($expect, $validator->isValid($input));

        if ($errorKey !== null) {
            self::assertArrayHasKey($errorKey, $validator->getMessages());
        }
    }

    public function testInvalidMinOptionIsExceptional(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Min/max date bounds must be either DateTime instances, or a string');
        new DateComparison([
            'min' => 'Bad news',
        ]);
    }

    public function testInvalidMaxOptionIsExceptional(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Min/max date bounds must be either DateTime instances, or a string');
        new DateComparison([
            'max' => 'Bad news',
        ]);
    }

    public function testThatOptionDateFormatsAreNotLenient(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Min/max date bounds must be either DateTime instances, or a string');
        new DateComparison([
            'max' => '2020-1-1',
        ]);
    }

    public function testZeroBoundsIsExceptional(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('At least one date boundary must be supplied');
        new DateComparison();
    }

    public function testThatTimezonesAreDiscarded(): void
    {
        $africa = new DateTimeZone('Africa/Johannesburg');

        $lower = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2020-01-01 10:00:00', $africa);
        self::assertInstanceOf(DateTimeImmutable::class, $lower);
        $upper = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2020-01-01 12:00:00', $africa);
        self::assertInstanceOf(DateTimeImmutable::class, $upper);

        $validator = new DateComparison([
            'min' => $lower,
            'max' => $upper,
        ]);

        $usa   = new DateTimeZone('America/New_York');
        $input = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2020-01-01 10:45:00', $usa);
        self::assertInstanceOf(DateTimeImmutable::class, $input);
        $utcEquivalent = $input->setTimezone(new DateTimeZone('UTC'));

        self::assertTrue($validator->isValid($input));
        self::assertFalse($validator->isValid($utcEquivalent));
    }
}
