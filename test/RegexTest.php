<?php

declare(strict_types=1);

namespace LaminasTest\Validator;

use Laminas\Validator\Exception\InvalidArgumentException;
use Laminas\Validator\Regex;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use function implode;
use function restore_error_handler;
use function set_error_handler;

final class RegexTest extends TestCase
{
    /**
     * Ensures that the validator follows expected behavior
     *
     * @param non-empty-string $pattern
     */
    #[DataProvider('basicDataProvider')]
    public function testBasic(string $pattern, mixed $input, bool $expected, string|null $errorKey): void
    {
        $validator = new Regex($pattern);

        self::assertSame($expected, $validator->isValid($input));

        if ($errorKey !== null) {
            self::assertArrayHasKey($errorKey, $validator->getMessages());
        }
    }

    /**
     * @psalm-return list<array{
     *     0: non-empty-string,
     *     1: mixed,
     *     2: bool,
     *     3: string|null,
     * }>
     */
    public static function basicDataProvider(): array
    {
        return [
            ['/[a-z]/', 'abc123', true, null],
            ['/[a-z]/', 'foo', true, null],
            ['/[a-z]/', 'a', true, null],
            ['/[a-z]/', 'z', true, null],
            ['/[a-z]/', '123', false, Regex::NOT_MATCH],
            ['/[a-z]/', 'A', false, Regex::NOT_MATCH],
            ['/[a-z]/', true, false, Regex::INVALID],
            ['/[a-z]/', ['foo'], false, Regex::INVALID],
            ['/^[0-9]+$/', 1, false, Regex::INVALID],
            ['/^[0-9]+$/', '1', true, null],
            ['/^[0-9]+$/', 1.234, false, Regex::INVALID],
            ['/^[0-9\.]+$/', 1.234, false, Regex::INVALID],
            ['/^[0-9\.]+$/', '1.234', true, null],
        ];
    }

    public function testBadPattern(): void
    {
        // phpcs:disable
        set_error_handler(static fn (int $_a, string $_b): bool => true);
        // phpcs:enable

        try {
            new Regex('/');
            self::fail('An exception should have been thrown');
        } catch (InvalidArgumentException $error) {
            self::assertStringContainsString('Internal error parsing', $error->getMessage());
        } finally {
            restore_error_handler();
        }
    }

    /**
     * @Laminas-11863
     */
    #[DataProvider('specialCharValidationProvider')]
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
    public static function specialCharValidationProvider(): array
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

    /**
     * @psalm-return array<string, array{0: string|array, 1: non-empty-string}>
     */
    public static function invalidConstructorArgumentsProvider(): array
    {
        return [
            'empty-string'             => ['', 'A regex pattern is required'],
            'missing-pattern-key'      => [[], "A regex pattern is required"],
            'pattern-key-not-string'   => [['pattern' => false], "A regex pattern is required"],
            'pattern-key-empty-string' => [['pattern' => ''], "A regex pattern is required"],
        ];
    }

    #[DataProvider('invalidConstructorArgumentsProvider')]
    public function testConstructorRaisesExceptionWhenProvidedInvalidArguments(
        string|array $options,
        string $expectedMessage,
    ): void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($expectedMessage);

        /** @psalm-suppress MixedArgumentTypeCoercion We are testing invalid options */
        new Regex($options);
    }

    public function testConstructorRaisesExceptionWhenProvidedWithInvalidOptionsArray(): void
    {
        $options = ['foo' => 'bar'];

        $this->expectException(InvalidArgumentException::class);

        /** @psalm-suppress InvalidArgument */
        new Regex($options);
    }
}
