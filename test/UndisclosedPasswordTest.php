<?php

declare(strict_types=1);

namespace LaminasTest\Validator;

use Exception;
use Laminas\Validator\UndisclosedPassword;
use LaminasTest\Validator\TestAsset\HttpClientException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use ReflectionClass;
use stdClass;

use function random_int;
use function sha1;
use function sprintf;
use function strtoupper;
use function substr;

/** @covers \Laminas\Validator\UndisclosedPassword */
final class UndisclosedPasswordTest extends TestCase
{
    /** @var ClientInterface&MockObject */
    private ClientInterface $httpClient;

    /** @var RequestFactoryInterface&MockObject */
    private RequestFactoryInterface $httpRequest;

    /** @var ResponseInterface&MockObject */
    private ResponseInterface $httpResponse;

    private UndisclosedPassword $validator;

    /** {@inheritDoc} */
    protected function setUp(): void
    {
        parent::setUp();

        $this->httpClient   = $this->createMock(ClientInterface::class);
        $this->httpRequest  = $this->createMock(RequestFactoryInterface::class);
        $this->httpResponse = $this->createMock(ResponseInterface::class);

        $this->validator = new UndisclosedPassword($this->httpClient, $this->httpRequest);
    }

    /**
     * @param non-empty-string $constant
     * @param class-string|object $classOrInstance
     * @return mixed
     */
    public function getConstant(string $constant, string|object $classOrInstance)
    {
        return (new ReflectionClass($classOrInstance))
            ->getConstant($constant);
    }

    /**
     * Data provider returning good, strong and unseen
     * passwords to be used in the validator.
     *
     * @psalm-return array<array{string}>
     */
    public function goodPasswordProvider(): array
    {
        return [
            ['ABi$B47es.Pfg3n9PjPi'],
            ['potence tipple would frisk shoofly'],
        ];
    }

    /**
     * Data provider for most common used passwords
     *
     * @see https://en.wikipedia.org/wiki/List_of_the_most_common_passwords
     *
     * @psalm-return array<array{string}>
     */
    public function seenPasswordProvider(): array
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
    public function testValidationFailsForInvalidInput(): void
    {
        self::assertFalse($this->validator->isValid(true));
        self::assertFalse($this->validator->isValid(new stdClass()));
        self::assertFalse($this->validator->isValid(['foo']));
    }

    /**
     * Test that a given password was not found in the HIBP
     * API service.
     *
     * @covers \Laminas\Validator\UndisclosedPassword
     * @dataProvider goodPasswordProvider
     */
    public function testStrongUnseenPasswordsPassValidation(string $password): void
    {
        $this->httpResponse
            ->expects(self::once())
            ->method('getBody')
            ->willReturnCallback(function (): string {
                $hash = sha1('laminas-validator');

                return sprintf(
                    '%s:%d',
                    strtoupper(substr($hash, $this->getConstant(
                        'HIBP_K_ANONYMITY_HASH_RANGE_LENGTH',
                        UndisclosedPassword::class
                    ))),
                    random_int(0, 100000)
                );
            });

        $this->httpClient
            ->expects(self::once())
            ->method('sendRequest')
            ->willReturn($this->httpResponse);

        self::assertTrue($this->validator->isValid($password));
    }

    /**
     * Test that a given password was already seen in the HIBP
     * AP service.
     *
     * @dataProvider seenPasswordProvider
     * @covers \Laminas\Validator\UndisclosedPassword
     * @covers \Laminas\Validator\AbstractValidator
     */
    public function testBreachedPasswordsDoNotPassValidation(string $password): void
    {
        $this->httpResponse
            ->expects(self::once())
            ->method('getBody')
            ->willReturnCallback(function () use ($password): string {
                $hash = sha1($password);

                return sprintf(
                    '%s:%d',
                    strtoupper(substr($hash, $this->getConstant(
                        'HIBP_K_ANONYMITY_HASH_RANGE_LENGTH',
                        UndisclosedPassword::class
                    ))),
                    random_int(0, 100000)
                );
            });

        $this->httpClient
            ->expects(self::once())
            ->method('sendRequest')
            ->willReturn($this->httpResponse);

        self::assertFalse($this->validator->isValid($password));
    }

    /**
     * Testing we are setting error messages when a password was found
     * in the breach database.
     *
     * @depends testBreachedPasswordsDoNotPassValidation
     * @dataProvider seenPasswordProvider
     * @covers \Laminas\Validator\UndisclosedPassword
     */
    public function testBreachedPasswordReturnErrorMessages(string $password): void
    {
        $this->httpClient
            ->expects(self::once())
            ->method('sendRequest')
            ->willThrowException(new Exception('foo'));

        $this->expectException(Exception::class);

        $this->validator->isValid($password);

        self::fail('Expected exception was not thrown');
    }

    /**
     * Test that the message templates are getting initialized via
     * the parent::_construct call
     */
    public function testMessageTemplatesAreInitialized(): void
    {
        self::assertNotEmpty($this->validator->getMessageTemplates());
    }

    /**
     * Testing that we capture any failures when trying to connect with
     * the HIBP web service.
     *
     * @depends testBreachedPasswordsDoNotPassValidation
     * @dataProvider seenPasswordProvider
     * @covers \Laminas\Validator\UndisclosedPassword
     */
    public function testValidationDegradesGracefullyWhenNoConnectionCanBeMade(string $password): void
    {
        $clientException = $this->createMock(HttpClientException::class);

        $this->httpClient
            ->expects(self::once())
            ->method('sendRequest')
            ->willThrowException($clientException);

        $this->expectException(ClientExceptionInterface::class);

        $this->validator->isValid($password);

        self::fail('Expected ClientException was not thrown');
    }
}
