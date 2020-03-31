<?php

/**
 * @see       https://github.com/laminas/laminas-validator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-validator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-validator/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Validator;

use Laminas\Validator\UndisclosedPassword;
use LaminasTest\Validator\TestAsset\HttpClientException;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use ReflectionClass;

class UndisclosedPasswordTest extends TestCase
{
    /**
     * @var ClientInterface
     */
    private $httpClient;

    /**
     * @var RequestInterface
     */
    private $httpRequest;

    /**
     * @var ResponseInterface
     */
    private $httpResponse;

    /**
     * @var UndisclosedPassword
     */
    private $validator;

    /**
     * @inheritDoc
     */
    protected function setUp() : void
    {
        $this->httpClient = $this->getMockBuilder(ClientInterface::class)
            ->getMockForAbstractClass();
        $this->httpRequest = $this->getMockBuilder(RequestFactoryInterface::class)
            ->getMockForAbstractClass();
        $this->httpResponse = $this->getMockBuilder(ResponseInterface::class)
            ->getMockForAbstractClass();

        $this->validator = new UndisclosedPassword($this->httpClient, $this->httpRequest);
    }

    /**
     * @inheritDoc
     */
    protected function tearDown() : void
    {
        $this->httpClient = null;
    }

    /**
     * @param string|object $classOrInstance
     * @return mixed
     */
    public function getConstant(string $constant, $classOrInstance)
    {
        $r = new ReflectionClass($classOrInstance);
        return $r->getConstant($constant);
    }

    /**
     * Data provider returning good, strong and unseen
     * passwords to be used in the validator.
     *
     * @return array
     */
    public function goodPasswordProvider()
    {
        return [
            ['ABi$B47es.Pfg3n9PjPi'],
            ['potence tipple would frisk shoofly'],
        ];
    }

    /**
     * Data provider for most common used passwords
     *
     * @return array
     * @see https://en.wikipedia.org/wiki/List_of_the_most_common_passwords
     */
    public function seenPasswordProvider()
    {
        return [
            ['123456'],
            ['password'],
            ['123456789'],
            ['12345678'],
            ['12345'],
        ];
    }

    /**
     * Testing that we reject invalid password types
     *
     * @covers \Laminas\Validator\UndisclosedPassword
     * @covers \Laminas\Validator\AbstractValidator
     * @todo Can be replaced by a \TypeError being thrown in PHP 7.0 or up
     */
    public function testValidationFailsForInvalidInput()
    {
        $this->assertFalse($this->validator->isValid(true));
        $this->assertFalse($this->validator->isValid(new \stdClass()));
        $this->assertFalse($this->validator->isValid(['foo']));
    }

    /**
     * Test that a given password was not found in the HIBP
     * API service.
     *
     * @param string $password
     *
     * @covers \Laminas\Validator\UndisclosedPassword
     * @dataProvider goodPasswordProvider
     */
    public function testStrongUnseenPasswordsPassValidation($password)
    {
        $this->httpResponse->method('getBody')
            ->willReturnCallback(function () use ($password) {
                $hash = \sha1('laminas-validator');
                return sprintf(
                    '%s:%d',
                    strtoupper(substr($hash, $this->getConstant(
                        'HIBP_K_ANONYMITY_HASH_RANGE_LENGTH',
                        UndisclosedPassword::class
                    ))),
                    rand(0, 100000)
                );
            });
        $this->httpClient->method('sendRequest')
            ->willReturn($this->httpResponse);

        $this->assertTrue($this->validator->isValid($password));
    }

    /**
     * Test that a given password was already seen in the HIBP
     * AP service.
     *
     * @param string $password
     * @dataProvider seenPasswordProvider
     * @covers \Laminas\Validator\UndisclosedPassword
     * @covers \Laminas\Validator\AbstractValidator
     */
    public function testBreachedPasswordsDoNotPassValidation($password)
    {
        $this->httpResponse->method('getBody')
            ->willReturnCallback(function () use ($password) {
                $hash = \sha1($password);
                return sprintf(
                    '%s:%d',
                    strtoupper(substr($hash, $this->getConstant(
                        'HIBP_K_ANONYMITY_HASH_RANGE_LENGTH',
                        UndisclosedPassword::class
                    ))),
                    rand(0, 100000)
                );
            });
        $this->httpClient->method('sendRequest')
            ->willReturn($this->httpResponse);

        $this->assertFalse($this->validator->isValid($password));
    }

    /**
     * Testing we are setting error messages when a password was found
     * in the breach database.
     *
     * @param string $password
     * @depends testBreachedPasswordsDoNotPassValidation
     * @dataProvider seenPasswordProvider
     * @covers \Laminas\Validator\UndisclosedPassword
     */
    public function testBreachedPasswordReturnErrorMessages($password)
    {
        $this->httpClient->method('sendRequest')
            ->will($this->throwException(new \Exception('foo')));

        $this->expectException(\Exception::class);
        $this->validator->isValid($password);
        $this->fail('Expected exception was not thrown');
    }

    /**
     * Test that the message templates are getting initialized via
     * the parent::_construct call
     */
    public function testMessageTemplatesAreInitialized() : void
    {
        $this->assertNotEmpty($this->validator->getMessageTemplates());
    }

    /**
     * Testing that we capture any failures when trying to connect with
     * the HIBP web service.
     *
     * @param string $password
     * @depends testBreachedPasswordsDoNotPassValidation
     * @dataProvider seenPasswordProvider
     * @covers \Laminas\Validator\UndisclosedPassword
     */
    public function testValidationDegradesGracefullyWhenNoConnectionCanBeMade($password)
    {
        $clientException = $this->getMockBuilder(HttpClientException::class)
            ->getMock();
        $this->httpClient->method('sendRequest')
            ->will($this->throwException($clientException));

        $this->expectException(ClientExceptionInterface::class);

        $this->validator->isValid($password);
        $this->fail('Expected ClientException was not thrown');
    }
}
