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

    public function testHasDefaultSettingsAndLazyLoadsUriHandler()
    {
        $validator = $this->validator;
        $uriHandler = $validator->getUriHandler();
        self::assertInstanceOf(UriHandler::class, $uriHandler);
        self::assertTrue($validator->getAllowRelative());
        self::assertTrue($validator->getAllowAbsolute());
    }

    public function testWithProperUriHandler()
    {
        $uriHandler = new UriHandler();
        $this->validator->setUriHandler($uriHandler);
        self::assertInstanceOf(UriHandler::class, $this->validator->getUriHandler());

        $this->validator->setUriHandler(UriHandler::class);
        self::assertInstanceOf(UriHandler::class, $this->validator->getUriHandler());
    }

    public function testConstructorWithArraySetsOptions()
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

    public function testConstructorWithArgsSetsOptions()
    {
        $uriMock = $this->createMock(UriHandler::class);
        $validator = new Validator\Uri($uriMock, false, false);
        self::assertSame($uriMock, $validator->getUriHandler());
        self::assertFalse($validator->getAllowRelative());
        self::assertFalse($validator->getAllowAbsolute());
    }

    public function testConstructWithTraversableSetsOptions()
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

    public function allowOptionsDataProvider()
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
     */
    public function testUriHandlerBehaviorWithAllowSettings(
        bool $allowAbsolute,
        bool $allowRelative,
        bool $isAbsolute,
        bool $isRelative,
        bool $isValid,
        bool $expects
    ) {
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

    public function testUriHandlerThrowsExceptionInParseMethodNotValid()
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

    public function testEqualsMessageTemplates()
    {
        $validator = $this->validator;
        self::assertObjectHasAttribute('messageTemplates', $validator);
        self::assertAttributeEquals($validator->getOption('messageTemplates'), 'messageTemplates', $validator);
    }

    public function testUriHandlerCanBeSpecifiedAsString()
    {
        $this->validator->setUriHandler(Http::class);
        self::assertInstanceOf(Http::class, $this->validator->getUriHandler());
    }

    public function testUriHandlerStringInvalidClassThrowsException()
    {
        $this->expectException(Validator\Exception\InvalidArgumentException::class);
        $this->validator->setUriHandler(\stdClass::class);
    }

    public function testUriHandlerInvalidTypeThrowsException()
    {
        $this->expectException(Validator\Exception\InvalidArgumentException::class);
        $this->validator->setUriHandler(new \stdClass());
    }

    public function testConstructUriHandlerStringInvalidClassThrowsException()
    {
        $this->expectException(Validator\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('Expecting a subclass name or instance of Laminas\Uri\Uri as $uriHandler');
        new Validator\Uri(\stdClass::class, false, false);
    }

    public function testConstructUriHandlerInvalidTypeThrowsException()
    {
        $this->expectException(Validator\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('Expecting a subclass name or instance of Laminas\Uri\Uri as $uriHandler');
        new Validator\Uri(new \stdClass(), false, false);
    }

    public function invalidValueTypes()
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
     */
    public function testIsValidReturnsFalseWhenProvidedUnsupportedType($value)
    {
        self::assertFalse($this->validator->isValid($value));
    }
}
