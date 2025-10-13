<?php

declare(strict_types=1);

namespace LaminasTest\Validator;

use Laminas\Validator\Exception\InvalidArgumentException;
use Laminas\Validator\StringLength;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use function chr;
use function restore_error_handler;
use function set_error_handler;

use const E_WARNING;

final class StringLengthTest extends TestCase
{
    /**
     * @return array<string, array{
     *     0: int,
     *     1: int|null,
     *     2: string,
     *     3: mixed,
     *     4: bool,
     *     5: string|null,
     * }>
     */
    public static function basicDataProvider(): array
    {
        return [
            'empty, utf-8, no-constraint'    => [0, null, 'utf-8', '', true, null],
            'empty, iso, no-constraint'      => [0, null, 'ISO-8859-16', '', true, null],
            'empty, min constraint'          => [1, null, 'utf-8', '', false, StringLength::TOO_SHORT],
            'non empty, no constraint'       => [0, null, 'utf-8', 'a', true, null],
            'non empty, no constraint - 2'   => [0, null, 'utf-8', 'aa', true, null],
            'non empty, min constraint'      => [1, null, 'utf-8', 'a', true, null],
            'too long'                       => [1, 2, 'utf-8', 'aaa', false, StringLength::TOO_LONG],
            'exact whitespace'               => [2, 2, 'utf-8', '  ', true, null],
            'exact non-empty'                => [2, 2, 'utf-8', 'aa', true, null],
            'exact utf8'                     => [3, 3, 'utf-8', 'äöü', true, null],
            'non empty utf8, max constraint' => [0, 6, 'utf-8', 'Müller', true, null],
            'utf8 - too long'                => [0, 6, 'utf-8', 'Müllered', false, null],
            'invalid arg: int'               => [0, 1, 'utf-8', 1, false, StringLength::INVALID],
            'invalid arg: float'             => [0, 1, 'utf-8', 0.123, false, StringLength::INVALID],
            'invalid arg: array'             => [0, 1, 'utf-8', ['foo'], false, StringLength::INVALID],
            'invalid arg: bool'              => [0, 1, 'utf-8', false, false, StringLength::INVALID],
            'invalid arg: null'              => [0, 1, 'utf-8', null, false, StringLength::INVALID],
            'invalid arg: object'            => [0, 1, 'utf-8', (object) [], false, StringLength::INVALID],
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

    public function testMalformedMultiByteDataWillCauseValidationFailure(): void
    {
        /**
         * Malformed UTF-8 will likely trigger errors/warnings in `ext-intl` or `ext-mbstring`
         *
         * Warnings are silenced to prevent the test from failing
         */
        // phpcs:disable
        set_error_handler(fn(int $_a, string $_b): bool => true, E_WARNING);
        // phpcs:enable

        $malformed = chr(0xED) . chr(0xA0) . chr(0x80);

        $validator = new StringLength();
        self::assertFalse($validator->isValid($malformed));
        self::assertArrayHasKey(StringLength::INVALID, $validator->getMessages());

        restore_error_handler();
    }
}
