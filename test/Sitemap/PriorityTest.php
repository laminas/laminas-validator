<?php

/**
 * @see       https://github.com/laminas/laminas-validator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-validator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-validator/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Validator\Sitemap;

use Laminas\Validator\Sitemap\Priority;
use PHPUnit\Framework\TestCase;

/**
 * @group      Laminas_Validator
 */
class PriorityTest extends TestCase
{
    /**
     * @var Priority
     */
    protected $validator;

    protected function setUp() : void
    {
        $this->validator = new Priority();
    }

    /**
     * Tests valid priorities
     *
     */
    public function testValidPriorities()
    {
        $values = [
            '0.0', '0.1', '0.2', '0.3', '0.4', '0.5',
            '0.6', '0.7', '0.8', '0.9', '1.0', '0.99',
            0.1, 0.6667, 0.0001, 0.4, 0, 1, .35,
        ];

        foreach ($values as $value) {
            $this->assertTrue($this->validator->isValid($value));
        }
    }

    /**
     * Tests invalid priorities
     *
     */
    public function testInvalidPriorities()
    {
        $values = [
            -1, -0.1, 1.1, 100, 10, 2, '3', '-4',
        ];

        foreach ($values as $value) {
            $this->assertFalse($this->validator->isValid($value));
            $messages = $this->validator->getMessages();
            $this->assertStringContainsString('is not a valid', current($messages));
        }
    }

    /**
     * Tests values that are no numbers
     *
     */
    public function testNotNumbers()
    {
        $values = [
            null, new \stdClass(), true, false, 'abcd',
        ];

        foreach ($values as $value) {
            $this->assertFalse($this->validator->isValid($value));
            $messages = $this->validator->getMessages();
            $this->assertStringContainsString('integer or float expected', current($messages));
        }
    }
}
