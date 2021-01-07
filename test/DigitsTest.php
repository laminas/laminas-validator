<?php

/**
 * @see       https://github.com/laminas/laminas-validator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-validator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-validator/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Validator;

use Laminas\Validator\Digits;
use PHPUnit\Framework\TestCase;

/**
 * @group      Laminas_Validator
 */
class DigitsTest extends TestCase
{
    /** @var Digits */
    protected $validator;

    protected function setUp() : void
    {
        $this->validator = new Digits();
    }

    /**
     * Ensures that the validator follows expected behavior for basic input values
     *
     * @dataProvider basicDataProvider
     * @return void
     */
    public function testExpectedResultsWithBasicInputValues(string $input, bool $expected)
    {
        $this->assertSame($expected, $this->validator->isValid($input));
    }

    /**
     * @psalm-return array<string, array{0: string, 1: bool}>
     */
    public function basicDataProvider(): array
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
     *
     * @return void
     */
    public function testMessagesEmptyInitially()
    {
        $this->assertEquals([], $this->validator->getMessages());
    }

    /**
     * @return void
     */
    public function testEmptyStringValueResultsInProperValidationFailureMessages()
    {
        $this->assertFalse($this->validator->isValid(''));
        $messages = $this->validator->getMessages();
        $arrayExpected = [
            Digits::STRING_EMPTY => 'The input is an empty string',
        ];
        $this->assertSame($arrayExpected, $messages);
    }

    /**
     * @return void
     */
    public function testInvalidValueResultsInProperValidationFailureMessages()
    {
        $this->assertFalse($this->validator->isValid('#'));
        $messages = $this->validator->getMessages();
        $arrayExpected = [
            Digits::NOT_DIGITS => 'The input must contain only digits',
        ];
        $this->assertSame($arrayExpected, $messages);
    }

    /**
     * @Laminas-4352
     *
     * @return void
     */
    public function testNonStringValidation(): void
    {
        $this->assertFalse($this->validator->isValid([1 => 1]));
    }

    public function testEqualsMessageTemplates(): void
    {
        $validator = $this->validator;
        $this->assertObjectHasAttribute('messageTemplates', $validator);
        $this->assertEquals($validator->getOption('messageTemplates'), $validator->getMessageTemplates());
    }
}
