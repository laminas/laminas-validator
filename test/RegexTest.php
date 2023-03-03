<?php

declare(strict_types=1);

namespace LaminasTest\Validator;

use Laminas\Validator\Exception\InvalidArgumentException;
use Laminas\Validator\Regex;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

use function array_keys;
use function implode;

use const PHP_INT_MAX;

/**
 * @group Laminas_Validator
 * @covers \Laminas\Validator\Regex
 */
final class RegexTest extends TestCase
{
    /**
     * Ensures that the validator follows expected behavior
     *
     * @dataProvider basicDataProvider
     */
    public function testBasic(array $options, string $input, bool $expected): void
    {
        $validator = new Regex(...$options);

        self::assertSame($expected, $validator->isValid($input));
    }

    /**
     * @psalm-return array<string, array{
     *     0: string[]|array<array-key, array<string, string>>,
     *     1: string,
     *     2: bool
     * }>
     */
    public function basicDataProvider(): array
    {
        return [
            // phpcs:disable
            'valid; abc123' => [['/[a-z]/'], 'abc123', true],
            'valid; foo'    => [['/[a-z]/'], 'foo',    true],
            'valid; a'      => [['/[a-z]/'], 'a',      true],
            'valid; z'      => [['/[a-z]/'], 'z',      true],

            'valid; 123' => [['/[a-z]/'], '123', false],
            'valid; A'   => [['/[a-z]/'], 'A',   false],

            'valid; abc123; array' => [[['pattern' => '/[a-z]/']], 'abc123', true],
            'valid; foo; array'    => [[['pattern' => '/[a-z]/']], 'foo', true],
            'valid; a; array'      => [[['pattern' => '/[a-z]/']], 'a', true],
            'valid; z; array'      => [[['pattern' => '/[a-z]/']], 'z', true],

            'valid; 123; array' => [[['pattern' => '/[a-z]/']], '123', false],
            'valid; A; array'   => [[['pattern' => '/[a-z]/']], 'A', false],
            // phpcs:enable
        ];
    }

    /**
     * Ensures that getMessages() returns expected default value
     */
    public function testGetMessages(): void
    {
        $validator = new Regex('/./');

        self::assertSame([], $validator->getMessages());
    }

    /**
     * Ensures that getPattern() returns expected value
     */
    public function testGetPattern(): void
    {
        $validator = new Regex('/./');

        self::assertSame('/./', $validator->getPattern());
    }

    /**
     * Ensures that a bad pattern results in a thrown exception upon isValid() call
     */
    public function testBadPattern(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Internal error parsing');

        new Regex('/');
    }

    /**
     * @Laminas-4352
     */
    public function testNonStringValidation(): void
    {
        $validator = new Regex('/./');

        self::assertFalse($validator->isValid([1 => 1]));
    }

    /**
     * @Laminas-11863
     * @dataProvider specialCharValidationProvider
     */
    public function testSpecialCharValidation(bool $expected, string $input): void
    {
        $validator = new Regex('/^[[:alpha:]\']+$/iu');

        self::assertSame(
            $expected,
            $validator->isValid($input),
            'Reason: ' . implode('', $validator->getMessages())
        );
    }

    /**
     * The elements of each array are, in order:
     *      - expected validation result
     *      - test input value
     *
     * @psalm-return array<array-key, array{0: bool, 1: string}>
     */
    public function specialCharValidationProvider(): array
    {
        return [
            [true, 'test'],
            [true, 'òèùtestòò'],
            [true, 'testà'],
            [true, 'teààst'],
            [true, 'ààòòìùéé'],
            [true, 'èùòìiieeà'],
            [false, 'test99'],
        ];
    }

    public function testEqualsMessageTemplates(): void
    {
        $validator = new Regex('//');

        self::assertSame(
            [
                Regex::INVALID,
                Regex::NOT_MATCH,
                Regex::ERROROUS,
            ],
            array_keys($validator->getMessageTemplates())
        );
        self::assertSame($validator->getOption('messageTemplates'), $validator->getMessageTemplates());
    }

    public function testEqualsMessageVariables(): void
    {
        $validator        = new Regex('//');
        $messageVariables = [
            'pattern' => 'pattern',
        ];

        self::assertSame($messageVariables, $validator->getOption('messageVariables'));
    }

    /**
     * @psalm-return array<string, array{0: mixed}>
     */
    public function invalidConstructorArgumentsProvider(): array
    {
        return [
            'true'       => [true],
            'false'      => [false],
            'zero'       => [0],
            'int'        => [1],
            'zero-float' => [0.0],
            'float'      => [1.0],
            'object'     => [(object) []],
        ];
    }

    /**
     * @dataProvider invalidConstructorArgumentsProvider
     */
    public function testConstructorRaisesExceptionWhenProvidedInvalidArguments(mixed $options): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Regex($options);
    }

    public function testConstructorRaisesExceptionWhenProvidedWithInvalidOptionsArray(): void
    {
        $options = ['foo' => 'bar'];

        $this->expectException(InvalidArgumentException::class);

        new Regex($options);
    }

    public function testIsValidShouldReturnFalseWhenRegexPatternIsInvalid(): void
    {
        $validator = new Regex('//');
        $pattern   = '/';

        $r = new ReflectionProperty($validator, 'pattern');
        $r->setAccessible(true);
        $r->setValue($validator, $pattern);

        self::assertFalse($validator->isValid('test'));
    }

    /**
     * @dataProvider numericDataProvider
     */
    public function testNumbers(string $pattern, int|float $input, bool $expected): void
    {
        $validator = new Regex($pattern);

        self::assertSame($expected, $validator->isValid($input));
    }

    /**
     * @psalm-return array<array-key, array{0: string, 1: int|float, 2: bool}>
     */
    public static function numericDataProvider(): array
    {
        return [
            ['/^(-?\d+(?:\.\d+)?+)$/', 12345, true],
            ['/^(-?\d+(?:\.\d+)?+)$/', PHP_INT_MAX, true],
            ['/^(-?\d+(?:\.\d+)?+)$/', -123, true],
            ['/^(-?\d+(?:\.\d+)?+)$/', 0.0099, true],
            ['/^(-?\d+(?:\.\d+)?+)$/', -100.50, true],
            ['/^\d+$/', 100, true],
            ['/^\d+$/', -100, false],
            ['/^\d+$/', 123.45, false],
        ];
    }
}
