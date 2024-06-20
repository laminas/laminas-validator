<?php

declare(strict_types=1);

namespace LaminasTest\Validator;

use Laminas\Validator\Exception\InvalidArgumentException;
use Laminas\Validator\NumberComparison;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use const PHP_INT_MAX;

class NumberComparisonTest extends TestCase
{
    public static function basicTestDataProvider(): array
    {
        return [
            [null, 10, true, 9, true, null],
            [null, 10, true, 10, true, null],
            [null, 10, true, 11, false, NumberComparison::ERROR_NOT_LESS_INCLUSIVE],
            [null, 10, false, 9, true, null],
            [null, 10, false, 10, false, NumberComparison::ERROR_NOT_LESS],
            [null, 10, false, 11, false, NumberComparison::ERROR_NOT_LESS],
            [10, null, true, 11, true, null],
            [10, null, true, 10, true, null],
            [10, null, true, 9, false, NumberComparison::ERROR_NOT_GREATER_INCLUSIVE],
            [10, null, false, 11, true, null],
            [10, null, false, 10, false, NumberComparison::ERROR_NOT_GREATER],
            [10, null, false, 9, false, NumberComparison::ERROR_NOT_GREATER],
            // Numerics should validate successfully
            [10, null, true, PHP_INT_MAX, true, null],
            [10, null, true, '99', true, null],
            [10, null, true, 99.9, true, null],
            [10, null, true, '99.9', true, null],
            // Non numerics should fail regardless of options
            [10, null, true, true, false, NumberComparison::ERROR_NOT_NUMERIC],
            [10, null, true, null, false, NumberComparison::ERROR_NOT_NUMERIC],
            [10, null, true, '', false, NumberComparison::ERROR_NOT_NUMERIC],
            [10, null, true, 'muppets', false, NumberComparison::ERROR_NOT_NUMERIC],
            [10, null, true, ['foo'], false, NumberComparison::ERROR_NOT_NUMERIC],
            [10, null, true, [1], false, NumberComparison::ERROR_NOT_NUMERIC],
            // Floats behave as expected both as options and input
            [10.0, 20.0, true, 10.1, true, null],
            [10.0, 20.0, true, 15, true, null],
            [10.0, 20.0, true, '15', true, null],
            [10.0, 20.0, true, 19.9999999, true, null],
            [10.0, 20.0, true, 4.2, false, NumberComparison::ERROR_NOT_GREATER_INCLUSIVE],
            [10.0, 20.0, true, 20.000001, false, NumberComparison::ERROR_NOT_LESS_INCLUSIVE],
            // Numeric strings behave as expected both as options and input
            ['10', '20', true, 10.1, true, null],
            ['10', '20', true, 15, true, null],
            ['10', '20', true, '15', true, null],
            ['10', '20', true, '19.9999999', true, null],
            ['10', '20', true, '4.2', false, NumberComparison::ERROR_NOT_GREATER_INCLUSIVE],
            ['10', '20', true, '20.000001', false, NumberComparison::ERROR_NOT_LESS_INCLUSIVE],
        ];
    }

    /**
     * @param numeric|null $min
     * @param numeric|null $max
     */
    #[DataProvider('basicTestDataProvider')]
    public function testBasic(
        int|float|string|null $min,
        int|float|string|null $max,
        bool $inclusive,
        mixed $input,
        bool $expect,
        string|null $errorKey,
    ): void {
        $validator = new NumberComparison([
            'min'       => $min,
            'max'       => $max,
            'inclusive' => $inclusive,
        ]);

        self::assertSame($expect, $validator->isValid($input));

        if ($errorKey === null) {
            return;
        }

        self::assertArrayHasKey($errorKey, $validator->getMessages());
    }

    public function testOmittingBothMinAndMaxIsExceptional(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('A numeric option value for either min, max or both must be provided');

        new NumberComparison();
    }

    public function testThatMinAndMaxMustBeSane(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The minimum constraint cannot be greater than the maximum constraint');

        new NumberComparison([
            'min' => 10,
            'max' => 5,
        ]);
    }
}
