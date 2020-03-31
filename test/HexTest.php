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
     * @return void
     */
    public function testBasic()
    {
        $valuesExpected = [
            [1, true],
            [0x1, true],
            [0x123, true],
            ['1', true],
            ['abc123', true],
            ['ABC123', true],
            ['1234567890abcdef', true],
            ['g', false],
            ['1.2', false],
        ];

        foreach ($valuesExpected as $element) {
            $this->assertEquals($element[1], $this->validator->isValid($element[0]), $element[0]);
        }
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
     */
    public function testNonStringValidation()
    {
        $this->assertFalse($this->validator->isValid([1 => 1]));
    }

    public function testEqualsMessageTemplates()
    {
        $validator = $this->validator;
        $this->assertAttributeEquals(
            $validator->getOption('messageTemplates'),
            'messageTemplates',
            $validator
        );
    }
}
