<?php

/**
 * @see       https://github.com/laminas/laminas-validator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-validator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-validator/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Validator\Sitemap;

use Laminas\Validator\Sitemap\Changefreq;
use PHPUnit\Framework\TestCase;

/**
 * @group      Laminas_Validator
 */
class ChangefreqTest extends TestCase
{
    /**
     * @var Changefreq
     */
    protected $validator;

    protected function setUp() : void
    {
        $this->validator = new Changefreq();
    }

    /**
     * Tests valid change frequencies
     *
     */
    public function testValidChangefreqs()
    {
        $values = [
            'always',  'hourly', 'daily', 'weekly',
            'monthly', 'yearly', 'never',
        ];

        foreach ($values as $value) {
            $this->assertTrue($this->validator->isValid($value));
        }
    }

    /**
     * Tests strings that should be invalid
     *
     */
    public function testInvalidStrings()
    {
        $values = [
            'alwayz',  '_hourly', 'Daily', 'wEekly',
            'mönthly ', ' yearly ', 'never ', 'rofl',
            'yesterday',
        ];

        foreach ($values as $value) {
            $this->assertFalse($this->validator->isValid($value));
            $messages = $this->validator->getMessages();
            $this->assertStringContainsString('is not a valid', current($messages));
        }
    }

    /**
     * Tests values that are not strings
     *
     */
    public function testNotString()
    {
        $values = [
            1, 1.4, null, new \stdClass(), true, false,
        ];

        foreach ($values as $value) {
            $this->assertFalse($this->validator->isValid($value));
            $messages = $this->validator->getMessages();
            $this->assertStringContainsString('String expected', current($messages));
        }
    }
}
