<?php

declare(strict_types=1);

namespace LaminasTest\Validator\Sitemap;

use Laminas\Validator\Sitemap\Loc;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use stdClass;

use function current;
use function str_repeat;

final class LocTest extends TestCase
{
    private Loc $validator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = new Loc();
    }

    /** @return list<array{0: string}> */
    public static function validLocs(): array
    {
        return [
            ['http://www.example.com'],
            ['http://www.example.com/'],
            ['http://www.exmaple.lan/'],
            ['https://www.exmaple.com/?foo=bar'],
            ['http://www.exmaple.com:8080/foo/bar/'],
            ['https://user:pass@www.exmaple.com:8080/'],
            ['https://www.example.com/?foo=%22bar%27&bar=%3Cbat%3E'],
        ];
    }

    #[DataProvider('validLocs')]
    public function testValidLocs(string $uri): void
    {
        self::assertTrue($this->validator->isValid($uri));
    }

    /**
     * @psalm-return list<array{0: string, 1: string}>
     */
    public static function invalidLocs(): array
    {
        return [
            ['www.example.com', Loc::NOT_VALID],
            ['/news/', Loc::NOT_VALID],
            ['#', Loc::NOT_VALID],
            ['http:/example.com/', Loc::NOT_VALID],
            ['https://www.example.com/?foo="bar\'&bar=<bat>', Loc::NOT_VALID],
            ['https://www.exmaple.com/?foo=&quot;bar&apos;&amp;bar=&lt;bat&gt;', Loc::NOT_VALID],
            ['https://www.example.com/' . str_repeat('foo', 2000), Loc::TOO_LONG],
        ];
    }

    #[DataProvider('invalidLocs')]
    public function testInvalidLocs(string $url, string $errorKey): void
    {
        self::assertFalse($this->validator->isValid($url), $url);

        $messages = $this->validator->getMessages();
        self::assertArrayHasKey($errorKey, $messages);
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
