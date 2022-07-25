<?php

declare(strict_types=1);

namespace LaminasTest\Validator\Sitemap;

use Laminas\Validator\Sitemap\Loc;
use PHPUnit\Framework\TestCase;
use stdClass;

use function current;

class LocTest extends TestCase
{
    /** @var Loc */
    protected $validator;

    protected function setUp(): void
    {
        $this->validator = new Loc();
    }

    /**
     * Tests valid locations
     */
    public function testValidLocs(): void
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

    /**
     * @psalm-return array<array-key, array{0: string}>
     */
    public static function invalidLocs(): array
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
     *
     * @todo A change in the URI API has led to most of these now validating
     * @dataProvider invalidLocs
     * @psalm-suppress UnevaluatedCode
     */
    public function testInvalidLocs(string $url): void
    {
        $this->markTestIncomplete('Test must be reworked');
        $this->assertFalse($this->validator->isValid($url), $url);
        $messages = $this->validator->getMessages();
        $this->assertStringContainsString('is not a valid', current($messages));
    }

    /**
     * Tests values that are not strings
     */
    public function testNotStrings(): void
    {
        $values = [
            1,
            1.4,
            null,
            new stdClass(),
            true,
            false,
        ];

        foreach ($values as $value) {
            $this->assertFalse($this->validator->isValid($value));
            $messages = $this->validator->getMessages();
            $this->assertStringContainsString('String expected', current($messages));
        }
    }
}
