<?php

declare(strict_types=1);

namespace LaminasTest\Validator;

use Laminas\Validator\Exception\InvalidArgumentException;
use Laminas\Validator\Uri;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class UriTest extends TestCase
{
    private Uri $validator;

    /**
     * Creates a new Uri Validator object for each test method
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = new Uri();
    }

    public function testConstructorWithArraySetsOptions(): void
    {
        $validator = new Uri([
            'allowRelative' => true,
            'allowAbsolute' => false,
        ]);

        self::assertTrue($validator->isValid('/foo'));
    }

    public function testDisallowingBothRelativeAndAbsoluteUrisIsExceptional(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Disallowing both relative and absolute uris means that no uris will be valid');

        new Uri([
            'allowRelative' => false,
            'allowAbsolute' => false,
        ]);
    }

    /**
     * @psalm-return list<array{
     *     0: string,
     *     1: bool,
     *     2: bool,
     *     3: bool,
     *     4: string|null,
     * }>
     */
    public static function allowOptionsDataProvider(): array
    {
        return [
            // Uri, allowAbsolute, allowRelative, isValid, errorKey
            // Empty String
            ['', true,         true,         false,  Uri::INVALID],
            ['', true,         false,        false,  Uri::INVALID],
            ['', false,        true,         false,  Uri::INVALID],
            ['https://www.example.com/foo', true, true, true, null],
            ['https://www.example.com/foo', false, true, false, Uri::NOT_RELATIVE],
            ['ftp://www.example.com/foo', true, true, true, null],
            ['ftp://www.example.com/foo', false, true, false, Uri::NOT_RELATIVE],
            ['/foo', true, false, false, Uri::NOT_ABSOLUTE],
            ['/foo', true, true, true, null],
            ['https:///baz', true, true, false, Uri::NOT_URI],
        ];
    }

    #[DataProvider('allowOptionsDataProvider')]
    public function testUriHandlerBehaviorWithAllowSettings(
        string $uri,
        bool $allowAbsolute,
        bool $allowRelative,
        bool $isValid,
        string|null $expectError,
    ): void {
        $validator = new Uri([
            'allowAbsolute' => $allowAbsolute,
            'allowRelative' => $allowRelative,
        ]);

        self::assertSame($isValid, $validator->isValid($uri));
        if (! $isValid) {
            self::assertNotNull($expectError, 'Expected an error key from the data provider');
            self::assertArrayHasKey($expectError, $validator->getMessages());
        }
    }

    /**
     * Ensures that getMessages() returns expected default value
     */
    public function testGetMessages(): void
    {
        self::assertSame([], $this->validator->getMessages());
    }

    /**
     * @psalm-return array<string, array{0: mixed}>
     */
    public static function invalidValueTypes(): array
    {
        return [
            'null'       => [null],
            'true'       => [true],
            'false'      => [false],
            'zero'       => [0],
            'int'        => [1],
            'zero-float' => [0.0],
            'float'      => [1.1],
            'array'      => [['https://example.com']],
            'object'     => [(object) ['uri' => 'https://example.com']],
        ];
    }

    #[DataProvider('invalidValueTypes')]
    public function testIsValidReturnsFalseWhenProvidedUnsupportedType(mixed $value): void
    {
        self::assertFalse($this->validator->isValid($value));
    }
}
