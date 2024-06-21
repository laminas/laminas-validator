<?php

declare(strict_types=1);

namespace LaminasTest\Validator;

use Laminas\Validator\Exception\InvalidArgumentException;
use Laminas\Validator\Exception\RuntimeException;
use Laminas\Validator\StringLength;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use function chr;
use function restore_error_handler;
use function set_error_handler;

use const E_WARNING;

final class StringLengthTest extends TestCase
{
    public static function basicDataProvider(): array
    {
        return [
            [0, null, 'utf-8', '', true, null],
            [0, null, 'ISO-8859-16', '', true, null],
            [1, null, 'utf-8', '', false, StringLength::TOO_SHORT],
            [0, null, 'utf-8', 'a', true, null],
            [0, null, 'utf-8', 'aa', true, null],
            [1, null, 'utf-8', 'a', true, null],
            [1, 2, 'utf-8', 'aaa', false, StringLength::TOO_LONG],
            [2, 2, 'utf-8', '  ', true, null],
            [2, 2, 'utf-8', 'aa', true, null],
            [3, 3, 'utf-8', 'äöü', true, null],
            [0, 6, 'utf-8', 'Müller', true, null],
            [0, 6, 'utf-8', 'Müllered', false, null],
            [0, 1, 'utf-8', 1, false, StringLength::INVALID],
            [0, 1, 'utf-8', 0.123, false, StringLength::INVALID],
            [0, 1, 'utf-8', ['foo'], false, StringLength::INVALID],
            [0, 1, 'utf-8', false, false, StringLength::INVALID],
            [0, 1, 'utf-8', null, false, StringLength::INVALID],
            [0, 1, 'utf-8', (object) [], false, StringLength::INVALID],
        ];
    }

    #[DataProvider('basicDataProvider')]
    public static function testBasicFunctionality(
        int $min,
        int|null $max,
        string $encoding,
        mixed $input,
        bool $isValid,
        string|null $errorKey,
    ): void {
        $validator = new StringLength([
            'min'      => $min,
            'max'      => $max,
            'encoding' => $encoding,
        ]);

        self::assertSame($isValid, $validator->isValid($input));

        if ($errorKey !== null) {
            self::assertArrayHasKey($errorKey, $validator->getMessages());
        }
    }

    public function testInvalidMaxOptionCausesException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The maximum must be greater than or equal to the minimum length');

        new StringLength(['min' => 10, 'max' => 5]);
    }

    public function testAnExceptionIsThrownWhenStringLengthCannotBeDetected(): void
    {
        /**
         * Malformed UTF-8 will likely trigger errors/warnings in `ext-intl` or `ext-mbstring`
         *
         * Warnings are silenced to prevent the test from failing
         */
        // phpcs:disable
        set_error_handler(function (int $_a, string $_b): bool { return true; }, E_WARNING);
        // phpcs:enable

        $malformed = chr(0xED) . chr(0xA0) . chr(0x80);
        try {
            (new StringLength())->isValid($malformed);
            self::fail('No exception was thrown');
        } catch (RuntimeException $error) {
            self::assertSame('Failed to detect string length', $error->getMessage());
        } finally {
            restore_error_handler();
        }
    }
}
