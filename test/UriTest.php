<?php

declare(strict_types=1);

namespace LaminasTest\Validator;

use Laminas\Uri\Exception\InvalidArgumentException;
use Laminas\Uri\Http;
use Laminas\Uri\Uri as UriHandler;
use Laminas\Validator\Exception\InvalidArgumentException as ValidatorInvalidArgumentException;
use Laminas\Validator\Uri;
use LaminasTest\Validator\TestAsset\CustomTraversable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use stdClass;

use function array_keys;

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

    public function testHasDefaultSettingsAndLazyLoadsUriHandler(): void
    {
        $uriHandler = $this->validator->getUriHandler();

        self::assertInstanceOf(UriHandler::class, $uriHandler);
        self::assertTrue($this->validator->getAllowRelative());
        self::assertTrue($this->validator->getAllowAbsolute());
    }

    public function testWithProperUriHandler(): void
    {
        $uriHandler = new UriHandler();
        $this->validator->setUriHandler($uriHandler);

        self::assertInstanceOf(UriHandler::class, $this->validator->getUriHandler());

        $this->validator->setUriHandler(UriHandler::class);

        self::assertInstanceOf(UriHandler::class, $this->validator->getUriHandler());
    }

    public function testConstructorWithArraySetsOptions(): void
    {
        $uriMock   = $this->createMock(UriHandler::class);
        $validator = new Uri([
            'uriHandler'    => $uriMock,
            'allowRelative' => false,
            'allowAbsolute' => false,
        ]);

        self::assertSame($uriMock, $validator->getUriHandler());
        self::assertFalse($validator->getAllowRelative());
        self::assertFalse($validator->getAllowAbsolute());
    }

    public function testConstructorWithArgsSetsOptions(): void
    {
        $uriMock   = $this->createMock(UriHandler::class);
        $validator = new Uri($uriMock, false, false);

        self::assertSame($uriMock, $validator->getUriHandler());
        self::assertFalse($validator->getAllowRelative());
        self::assertFalse($validator->getAllowAbsolute());
    }

    public function testConstructWithTraversableSetsOptions(): void
    {
        $uriMock   = $this->createMock(UriHandler::class);
        $options   = new CustomTraversable([
            'uriHandler'    => $uriMock,
            'allowRelative' => false,
            'allowAbsolute' => false,
        ]);
        $validator = new Uri($options);

        self::assertSame($uriMock, $validator->getUriHandler());
        self::assertFalse($validator->getAllowRelative());
        self::assertFalse($validator->getAllowAbsolute());
    }

    /**
     * @psalm-return array<array-key, array{
     *     0: bool,
     *     1: bool,
     *     2: bool,
     *     3: bool,
     *     4: bool,
     *     5: bool
     * }>
     */
    public static function allowOptionsDataProvider(): array
    {
        return [
            //    allowAbsolute allowRelative isAbsolute isRelative isValid expects
            [true,         true,         true,      false,     true,   true],
            [true,         true,         false,     true,      true,   true],
            [false,        true,         true,      false,     true,   false],
            [false,        true,         false,     true,      true,   true],
            [true,         false,        true,      false,     true,   true],
            [true,         false,        false,     true,      true,   false],
            [false,        false,        true,      false,     true,   false],
            [false,        false,        false,     true,      true,   false],
            [true,         true,         false,     false,     false,  false],
        ];
    }

    #[DataProvider('allowOptionsDataProvider')]
    public function testUriHandlerBehaviorWithAllowSettings(
        bool $allowAbsolute,
        bool $allowRelative,
        bool $isAbsolute,
        bool $isRelative,
        bool $isValid,
        bool $expects
    ): void {
        $uriMock = $this->getMockBuilder(UriHandler::class)
            ->setConstructorArgs(['parse', 'isValid', 'isAbsolute', 'isValidRelative'])
            ->getMock();

        $uriMock
            ->expects(self::once())
            ->method('isValid')
            ->willReturn($isValid);

        $uriMock
            ->expects(self::any())
            ->method('isAbsolute')
            ->willReturn($isAbsolute);

        $uriMock
            ->expects(self::any())
            ->method('isValidRelative')
            ->willReturn($isRelative);

        $this->validator
            ->setUriHandler($uriMock)
            ->setAllowAbsolute($allowAbsolute)
            ->setAllowRelative($allowRelative);

        self::assertSame($expects, $this->validator->isValid('uri'));
    }

    public function testUriHandlerThrowsExceptionInParseMethodNotValid(): void
    {
        $uriMock = $this->createMock(UriHandler::class);
        $uriMock
            ->expects(self::once())
            ->method('parse')
            ->willThrowException(new InvalidArgumentException());

        $this->validator->setUriHandler($uriMock);

        self::assertFalse($this->validator->isValid('uri'));
    }

    /**
     * Ensures that getMessages() returns expected default value
     */
    public function testGetMessages(): void
    {
        self::assertSame([], $this->validator->getMessages());
    }

    public function testEqualsMessageTemplates(): void
    {
        self::assertSame(
            [
                Uri::INVALID,
                Uri::NOT_URI,
            ],
            array_keys($this->validator->getMessageTemplates())
        );
        self::assertSame($this->validator->getOption('messageTemplates'), $this->validator->getMessageTemplates());
    }

    public function testUriHandlerCanBeSpecifiedAsString(): void
    {
        $this->validator->setUriHandler(Http::class);

        self::assertInstanceOf(Http::class, $this->validator->getUriHandler());
    }

    public function testUriHandlerStringInvalidClassThrowsException(): void
    {
        $this->expectException(ValidatorInvalidArgumentException::class);
        /** @psalm-suppress InvalidArgument */
        $this->validator->setUriHandler(stdClass::class);
    }

    public function testUriHandlerInvalidTypeThrowsException(): void
    {
        $this->expectException(ValidatorInvalidArgumentException::class);
        /** @psalm-suppress InvalidArgument */
        $this->validator->setUriHandler(new stdClass());
    }

    public function testConstructUriHandlerStringInvalidClassThrowsException(): void
    {
        $this->expectException(ValidatorInvalidArgumentException::class);
        $this->expectExceptionMessage('Expecting a subclass name or instance of Laminas\Uri\Uri as $uriHandler');
        /** @psalm-suppress InvalidArgument */
        new Uri(stdClass::class, false, false);
    }

    public function testConstructUriHandlerInvalidTypeThrowsException(): void
    {
        $this->expectException(ValidatorInvalidArgumentException::class);
        $this->expectExceptionMessage('Expecting a subclass name or instance of Laminas\Uri\Uri as $uriHandler');

        new Uri(new stdClass(), false, false);
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
            'array'      => [['http://example.com']],
            'object'     => [(object) ['uri' => 'http://example.com']],
        ];
    }

    #[DataProvider('invalidValueTypes')]
    public function testIsValidReturnsFalseWhenProvidedUnsupportedType(mixed $value): void
    {
        self::assertFalse($this->validator->isValid($value));
    }
}
