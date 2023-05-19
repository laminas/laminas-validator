<?php

declare(strict_types=1);

namespace LaminasTest\Validator;

use Laminas\Validator\Digits;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use function array_keys;

final class DigitsTest extends TestCase
{
    private Digits $validator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = new Digits();
    }

    /**
     * Ensures that the validator follows expected behavior for basic input values
     */
    #[DataProvider('basicDataProvider')]
    public function testExpectedResultsWithBasicInputValues(string $input, bool $expected): void
    {
        self::assertSame($expected, $this->validator->isValid($input));
    }

    /**
     * @psalm-return array<string, array{0: string, 1: bool}>
     */
    public static function basicDataProvider(): array
    {
        return [
            // phpcs:disable
            'invalid; starts with alphabetic chars'                 => ['abc123',  false],
            'invalid; contains alphabetic chars and one whitespace' => ['abc 123', false],
            'invalid; contains only alphabetic chars'               => ['abcxyz',  false],
            'invalid; contains alphabetic and special chars'        => ['AZ@#4.3', false],
            'invalid; is a float'                                   => ['1.23',    false],
            'invalid; is a hexa notation'                           => ['0x9f',    false],
            'invalid; is empty'                                     => ['',        false],

            'valid; is a normal integer'                            => ['123',     true],
            'valid; starts with a zero'                             => ['09',      true],
            // phpcs:enable
        ];
    }

    /**
     * Ensures that getMessages() returns expected initial value
     */
    public function testMessagesEmptyInitially(): void
    {
        self::assertSame([], $this->validator->getMessages());
    }

    public function testEmptyStringValueResultsInProperValidationFailureMessages(): void
    {
        self::assertFalse($this->validator->isValid(''));

        $messages      = $this->validator->getMessages();
        $arrayExpected = [
            Digits::STRING_EMPTY => 'The input is an empty string',
        ];

        self::assertSame($arrayExpected, $messages);
    }

    public function testInvalidValueResultsInProperValidationFailureMessages(): void
    {
        self::assertFalse($this->validator->isValid('#'));

        $messages      = $this->validator->getMessages();
        $arrayExpected = [
            Digits::NOT_DIGITS => 'The input must contain only digits',
        ];

        self::assertSame($arrayExpected, $messages);
    }

    /**
     * @Laminas-4352
     */
    public function testNonStringValidation(): void
    {
        self::assertFalse($this->validator->isValid([1 => 1]));
    }

    public function testEqualsMessageTemplates(): void
    {
        self::assertSame(
            [
                Digits::NOT_DIGITS,
                Digits::STRING_EMPTY,
                Digits::INVALID,
            ],
            array_keys($this->validator->getMessageTemplates())
        );
        self::assertSame($this->validator->getOption('messageTemplates'), $this->validator->getMessageTemplates());
    }
}
