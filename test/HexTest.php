<?php

/**
 * @see       https://github.com/laminas/laminas-validator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-validator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-validator/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Validator;

use Laminas\Validator\Hex;
use PHPUnit\Framework\TestCase;

/**
 * @group      Laminas_Validator
 */
class HexTest extends TestCase
{
    /**
     * @var Hex
     */
    protected $validator;

    protected function setUp() : void
    {
        $this->validator = new Hex();
    }

    /**
     * Ensures that the validator follows expected behavior
     *
     * @dataProvider basicDataProvider
     * @param int|string $input
     * @return void
     */
    public function testBasic($input, bool $expected)
    {
        $this->assertSame($expected, $this->validator->isValid($input));
    }

    /**
     * @psalm-return array<string, array{
     *     0: mixed,
     *     1: bool
     * }>
     */
    public function basicDataProvider(): array
    {
        return [
            // phpcs:disable
            'valid; int; 1' => [1, true],
            'valid; hex; 0x1' => [0x1, true],
            'valid; hex; 0x123' => [0x123, true],
            'valid; string; 1' => ['1', true],
            'valid; string; abc123' => ['abc123', true],
            'valid; string; ABC123' => ['ABC123', true],
            'valid; string; 1234567890abcdef' => ['1234567890abcdef', true],

            'invalid; string; g' => ['g', false],
            'invalid; string; 1.2' => ['1.2', false],
            // phpcs:enable
        ];
    }

    /**
     * Ensures that getMessages() returns expected default value
     *
     * @return void
     */
    public function testGetMessages()
    {
        $this->assertEquals([], $this->validator->getMessages());
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
