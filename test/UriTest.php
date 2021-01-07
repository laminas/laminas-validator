<?php

/**
 * @see       https://github.com/laminas/laminas-validator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-validator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-validator/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Validator;

use Laminas\Uri\Exception\InvalidArgumentException;
use Laminas\Uri\Http;
use Laminas\Uri\Uri as UriHandler;
use Laminas\Validator;
use PHPUnit\Framework\TestCase;

/**
 * @group      Laminas_Validator
 */
class UriTest extends TestCase
{
    /**
     * @var \Laminas\Validator\Uri
     */
    protected $validator;

    /**
     * Creates a new Uri Validator object for each test method
     *
     * @return void
     */
    protected function setUp() : void
    {
        $this->validator = new Validator\Uri();
    }

    public function testHasDefaultSettingsAndLazyLoadsUriHandler(): void
    {
        $validator = $this->validator;
        $uriHandler = $validator->getUriHandler();
        self::assertInstanceOf(UriHandler::class, $uriHandler);
        self::assertTrue($validator->getAllowRelative());
        self::assertTrue($validator->getAllowAbsolute());
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
        $uriMock = $this->createMock(UriHandler::class);
        $validator = new Validator\Uri([
            'uriHandler' => $uriMock,
            'allowRelative' => false,
            'allowAbsolute' => false,
        ]);
        self::assertSame($uriMock, $validator->getUriHandler());
        self::assertFalse($validator->getAllowRelative());
        self::assertFalse($validator->getAllowAbsolute());
    }

    public function testConstructorWithArgsSetsOptions(): void
    {
        $uriMock = $this->createMock(UriHandler::class);
        $validator = new Validator\Uri($uriMock, false, false);
        self::assertSame($uriMock, $validator->getUriHandler());
        self::assertFalse($validator->getAllowRelative());
        self::assertFalse($validator->getAllowAbsolute());
    }

    public function testConstructWithTraversableSetsOptions(): void
    {
        $uriMock = $this->createMock(UriHandler::class);
        $options = new TestAsset\CustomTraversable([
            'uriHandler' => $uriMock,
            'allowRelative' => false,
            'allowAbsolute' => false,
        ]);
        $validator = new Validator\Uri($options);
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
    public function allowOptionsDataProvider(): array
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

    /**
     * @dataProvider allowOptionsDataProvider
     *
     * @return void
     */
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
            ->expects($this->once())
            ->method('isValid')
            ->willReturn($isValid);
        $uriMock
            ->method('isAbsolute')
            ->willReturn($isAbsolute);
        $uriMock
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
        $uriMock->expects($this->once())
            ->method('parse')
            ->will($this->throwException(new InvalidArgumentException()));

        $this->validator->setUriHandler($uriMock);
        self::assertFalse($this->validator->isValid('uri'));
    }

    /**
     * Ensures that getMessages() returns expected default value
     *
     * @return void
     */
    public function testGetMessages()
    {
        self::assertSame([], $this->validator->getMessages());
    }

    public function testEqualsMessageTemplates(): void
    {
        $validator = $this->validator;
        self::assertObjectHasAttribute('messageTemplates', $validator);
        self::assertEquals($validator->getOption('messageTemplates'), $validator->getMessageTemplates());
    }

    public function testUriHandlerCanBeSpecifiedAsString(): void
    {
        $this->validator->setUriHandler(Http::class);
        self::assertInstanceOf(Http::class, $this->validator->getUriHandler());
    }

    public function testUriHandlerStringInvalidClassThrowsException(): void
    {
        $this->expectException(Validator\Exception\InvalidArgumentException::class);
        $this->validator->setUriHandler(\stdClass::class);
    }

    public function testUriHandlerInvalidTypeThrowsException(): void
    {
        $this->expectException(Validator\Exception\InvalidArgumentException::class);
        $this->validator->setUriHandler(new \stdClass());
    }

    public function testConstructUriHandlerStringInvalidClassThrowsException(): void
    {
        $this->expectException(Validator\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('Expecting a subclass name or instance of Laminas\Uri\Uri as $uriHandler');
        new Validator\Uri(\stdClass::class, false, false);
    }

    public function testConstructUriHandlerInvalidTypeThrowsException(): void
    {
        $this->expectException(Validator\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('Expecting a subclass name or instance of Laminas\Uri\Uri as $uriHandler');
        new Validator\Uri(new \stdClass(), false, false);
    }

    /**
     * @psalm-return array<string, array{0: mixed}>
     */
    public function invalidValueTypes(): array
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

    /**
     * @dataProvider invalidValueTypes
     *
     * @return void
     */
    public function testIsValidReturnsFalseWhenProvidedUnsupportedType($value): void
    {
        self::assertFalse($this->validator->isValid($value));
    }
}
