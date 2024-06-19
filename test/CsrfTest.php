<?php

declare(strict_types=1);

namespace LaminasTest\Validator;

use Laminas\Session\Config\StandardConfig;
use Laminas\Session\Container;
use Laminas\Session\Storage\ArrayStorage;
use Laminas\Validator\Csrf;
use LaminasTest\Validator\TestAsset\SessionManager;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

use function class_exists;
use function md5;
use function sprintf;
use function str_replace;
use function strtr;
use function uniqid;

/**
 * @deprecated
 *
 * @psalm-suppress DeprecatedClass
 */
final class CsrfTest extends TestCase
{
    private Csrf $validator;

    private SessionManager $sessionManager;

    protected function setUp(): void
    {
        parent::setUp();

        // Setup session handling
        $_SESSION             = [];
        $sessionManager       = new SessionManager(
            new StandardConfig(),
            new ArrayStorage()
        );
        $this->sessionManager = $sessionManager;
        Container::setDefaultManager($sessionManager);

        $this->validator = new Csrf();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        if (! class_exists(Container::class)) {
            return;
        }

        $_SESSION = [];
        Container::setDefaultManager(null);
    }

    public function testSaltHasDefaultValueIfNotSet(): void
    {
        self::assertSame('salt', $this->validator->getSalt());
    }

    public function testSaltIsMutable(): void
    {
        $this->validator->setSalt('pepper');

        self::assertSame('pepper', $this->validator->getSalt());
    }

    public function testSessionContainerIsLazyLoadedIfNotSet(): void
    {
        $container = $this->validator->getSession();

        self::assertInstanceOf(Container::class, $container);
    }

    public function testSessionContainerIsMutable(): void
    {
        $container = new Container('foo', $this->sessionManager);
        $this->validator->setSession($container);

        self::assertSame($container, $this->validator->getSession());
    }

    public function testNameHasDefaultValue(): void
    {
        self::assertSame('csrf', $this->validator->getName());
    }

    public function testNameIsMutable(): void
    {
        $this->validator->setName('foo');

        self::assertSame('foo', $this->validator->getName());
    }

    public function testTimeoutHasDefaultValue(): void
    {
        self::assertSame(300, $this->validator->getTimeout());
    }

    /**
     * @return (int|null|string)[][]
     * @psalm-return array<array-key, array{0: null|int|string, 1: null|int}>
     */
    public static function timeoutValuesDataProvider(): array
    {
        return [
            //    timeout  expected
            [600,     600],
            [null,    null],
            ['0',     0],
            ['100',   100],
        ];
    }

    /**
     * @param null|int|string $timeout
     */
    #[DataProvider('timeoutValuesDataProvider')]
    public function testTimeoutIsMutable($timeout, ?int $expected): void
    {
        $this->validator->setTimeout($timeout);

        self::assertSame($expected, $this->validator->getTimeout());
    }

    public function testAllOptionsMayBeSetViaConstructor(): void
    {
        $container = new Container('foo', $this->sessionManager);
        $options   = [
            'name'    => 'hash',
            'salt'    => 'hashful',
            'session' => $container,
            'timeout' => 600,
        ];
        $validator = new Csrf($options);

        foreach ($options as $key => $value) {
            if ($key === 'session') {
                self::assertSame($container, $value);

                continue;
            }

            $method = 'get' . $key;

            self::assertSame($value, $validator->$method());
        }
    }

    public function testHashIsGeneratedOnFirstRetrieval(): void
    {
        $hash = $this->validator->getHash();

        self::assertNotEmpty($hash);

        $test = $this->validator->getHash();

        self::assertSame($hash, $test);
    }

    public function testSessionNameIsDerivedFromClassSaltAndName(): void
    {
        $class    = $this->validator::class;
        $class    = str_replace('\\', '_', $class);
        $expected = sprintf('%s_%s_%s', $class, $this->validator->getSalt(), $this->validator->getName());

        self::assertSame($expected, $this->validator->getSessionName());
    }

    public function testSessionNameRemainsValidForElementBelongingToFieldset(): void
    {
        $this->validator->setName('fieldset[csrf]');
        $class    = $this->validator::class;
        $class    = str_replace('\\', '_', $class);
        $name     = strtr($this->validator->getName(), ['[' => '_', ']' => '']);
        $expected = sprintf('%s_%s_%s', $class, $this->validator->getSalt(), $name);

        self::assertSame($expected, $this->validator->getSessionName());
    }

    public function testIsValidReturnsFalseWhenValueDoesNotMatchHash(): void
    {
        self::assertFalse($this->validator->isValid('foo'));
    }

    public function testValidationErrorMatchesNotSameConstantAndRelatedMessage(): void
    {
        $this->validator->isValid('foo');
        $messages = $this->validator->getMessages();

        self::assertArrayHasKey(Csrf::NOT_SAME, $messages);
        self::assertSame('The form submitted did not originate from the expected site', $messages[Csrf::NOT_SAME]);
    }

    public function testIsValidReturnsTrueWhenValueMatchesHash(): void
    {
        $hash = $this->validator->getHash();

        self::assertTrue($this->validator->isValid($hash));
    }

    public function testSessionContainerContainsHashAfterHashHasBeenGenerated(): void
    {
        $hash      = $this->validator->getHash();
        $container = $this->validator->getSession();
        $test      = $container->hash; // Doing this, as expiration hops are 1; have to grab on first access

        self::assertSame($hash, $test);
    }

    public function testSettingNewSessionContainerSetsHashInNewContainer(): void
    {
        $hash      = $this->validator->getHash();
        $container = new Container('foo', $this->sessionManager);
        $this->validator->setSession($container);
        $test = $container->hash; // Doing this, as expiration hops are 1; have to grab on first access

        self::assertSame($hash, $test);
    }

    public function testMultipleValidatorsSharingContainerGenerateDifferentHashes(): void
    {
        $validatorOne = new Csrf();
        $validatorTwo = new Csrf();

        $containerOne = $validatorOne->getSession();
        $containerTwo = $validatorOne->getSession();

        self::assertSame($containerOne, $containerTwo);

        $hashOne = $validatorOne->getHash();
        $hashTwo = $validatorTwo->getHash();

        self::assertNotSame($hashOne, $hashTwo);
    }

    public function testCanValidateAnyHashWithinTheSameContainer(): void
    {
        $validatorOne = new Csrf();
        $validatorTwo = new Csrf();

        $hashOne = $validatorOne->getHash();
        $hashTwo = $validatorTwo->getHash();

        self::assertTrue($validatorOne->isValid($hashOne));
        self::assertTrue($validatorOne->isValid($hashTwo));
        self::assertTrue($validatorTwo->isValid($hashOne));
        self::assertTrue($validatorTwo->isValid($hashTwo));
    }

    public function testCannotValidateHashesOfOtherContainers(): void
    {
        $validatorOne = new Csrf();
        $validatorTwo = new Csrf(['name' => 'foo']);

        $containerOne = $validatorOne->getSession();
        $containerTwo = $validatorTwo->getSession();

        self::assertNotSame($containerOne, $containerTwo);

        $hashOne = $validatorOne->getHash();
        $hashTwo = $validatorTwo->getHash();

        self::assertTrue($validatorOne->isValid($hashOne));
        self::assertFalse($validatorOne->isValid($hashTwo));
        self::assertFalse($validatorTwo->isValid($hashOne));
        self::assertTrue($validatorTwo->isValid($hashTwo));
    }

    public function testCannotReValidateAnExpiredHash(): void
    {
        $hash = $this->validator->getHash();

        self::assertTrue($this->validator->isValid($hash));
        $requestTime = $_SERVER['REQUEST_TIME'] ?? null;
        self::assertIsNumeric($requestTime);

        $this->sessionManager->getStorage()->setMetadata(
            $this->validator->getSession()->getName(),
            ['EXPIRE' => $requestTime - 18600]
        );

        self::assertFalse($this->validator->isValid($hash));
    }

    public function testCanValidateHashWithoutId(): void
    {
        $method = new ReflectionMethod($this->validator::class, 'getTokenFromHash');

        $hash      = $this->validator->getHash();
        $bareToken = $method->invoke($this->validator, $hash);

        self::assertTrue($this->validator->isValid($bareToken));
    }

    public function testCanRejectArrayValues(): void
    {
        self::assertFalse($this->validator->isValid([]));
    }

    /**
     * @return string[][]
     * @psalm-return array<array-key, array{0: string}>
     */
    public static function fakeValuesDataProvider(): array
    {
        return [
            [''],
            ['-fakeTokenId'],
            ['fakeTokenId-fakeTokenId'],
            ['fakeTokenId-'],
            ['fakeTokenId'],
            [md5(uniqid()) . '-'],
            [md5(uniqid()) . '-' . md5(uniqid())],
            ['-' . md5(uniqid())],
        ];
    }

    #[DataProvider('fakeValuesDataProvider')]
    public function testWithFakeValues(string $value): void
    {
        $validator = new Csrf();

        self::assertFalse($validator->isValid($value));
    }
}
