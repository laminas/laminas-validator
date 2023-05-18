<?php

declare(strict_types=1);

namespace LaminasTest\Validator\Sitemap;

use Laminas\Validator\Sitemap\Loc;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use stdClass;

use function current;

final class LocTest extends TestCase
{
    private Loc $validator;

    protected function setUp(): void
    {
        parent::setUp();

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
            self::assertTrue($this->validator->isValid($value));
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
     */
    #[DataProvider('invalidLocs')]
    public function testInvalidLocs(string $url): void
    {
        self::markTestIncomplete('Test must be reworked');

        self::assertFalse($this->validator->isValid($url), $url);

        $messages = $this->validator->getMessages();

        self::assertStringContainsString('is not a valid', current($messages));
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
            self::assertFalse($this->validator->isValid($value));

            $messages = $this->validator->getMessages();

            self::assertStringContainsString('String expected', current($messages));
        }
    }
}
