<?php

/**
 * @see       https://github.com/laminas/laminas-validator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-validator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-validator/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Validator\Sitemap;

use Laminas\Validator\Sitemap\Loc;
use PHPUnit\Framework\TestCase;

/**
 * @group      Laminas_Validator
 */
class LocTest extends TestCase
{
    /**
     * @var Loc
     */
    protected $validator;

    protected function setUp() : void
    {
        $this->validator = new Loc();
    }

    /**
     * Tests valid locations
     *
     */
    public function testValidLocs()
    {
        $values = [
            'http://www.example.com',
            'http://www.example.com/',
            'http://www.exmaple.lan/',
            'https://www.exmaple.com/?foo=bar',
            'http://www.exmaple.com:8080/foo/bar/',
            'https://user:pass@www.exmaple.com:8080/',
            'https://www.exmaple.com/?foo=&quot;bar&apos;&amp;bar=&lt;bat&gt;',
        ];

        foreach ($values as $value) {
            $this->assertTrue($this->validator->isValid($value));
        }
    }

    public static function invalidLocs()
    {
        return [
            ['www.example.com'],
            ['/news/'],
            ['#'],
            ['http:/example.com/'],
            ['https://www.exmaple.com/?foo="bar\'&bar=<bat>'],
        ];
    }

    /**
     * Tests invalid locations
     * @todo A change in the URI API has led to most of these now validating
     * @dataProvider invalidLocs
     */
    public function testInvalidLocs($url)
    {
        $this->markTestIncomplete('Test must be reworked');
        $this->assertFalse($this->validator->isValid($url), $url);
        $messages = $this->validator->getMessages();
        $this->assertStringContainsString('is not a valid', current($messages));
    }

    /**
     * Tests values that are not strings
     *
     */
    public function testNotStrings()
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
