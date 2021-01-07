<?php

/**
 * @see       https://github.com/laminas/laminas-validator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-validator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-validator/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Validator;

use Laminas\Session\Config\StandardConfig;
use Laminas\Session\Container;
use Laminas\Session\Storage\ArrayStorage;
use Laminas\Validator\Csrf;
use PHPUnit\Framework\TestCase;

/**
 * Laminas\Csrf
 *
 * @group      Laminas_Validator
 */
class CsrfTest extends TestCase
{
    /** @var Csrf */
    public $validator;

    /** @var TestAsset\SessionManager */
    public $sessionManager;

    protected function setUp() : void
    {
        // Setup session handling
        $_SESSION = [];
        $sessionConfig = new StandardConfig([
            'storage' => ArrayStorage::class,
        ]);
        $sessionManager       = new TestAsset\SessionManager($sessionConfig);
        $this->sessionManager = $sessionManager;
        Container::setDefaultManager($sessionManager);

        $this->validator = new Csrf;
    }

    protected function tearDown() : void
    {
        if (! class_exists(Container::class)) {
            return;
        }

        $_SESSION = [];
        Container::setDefaultManager(null);
    }

    public function testSaltHasDefaultValueIfNotSet(): void
    {
        $this->assertEquals('salt', $this->validator->getSalt());
    }

    public function testSaltIsMutable(): void
    {
        $this->validator->setSalt('pepper');
        $this->assertEquals('pepper', $this->validator->getSalt());
    }

    public function testSessionContainerIsLazyLoadedIfNotSet(): void
    {
        $container = $this->validator->getSession();
        $this->assertInstanceOf(Container::class, $container);
    }

    public function testSessionContainerIsMutable(): void
    {
        $container = new Container('foo', $this->sessionManager);
        $this->validator->setSession($container);
        $this->assertSame($container, $this->validator->getSession());
    }

    public function testNameHasDefaultValue(): void
    {
        $this->assertEquals('csrf', $this->validator->getName());
    }

    public function testNameIsMutable(): void
    {
        $this->validator->setName('foo');
        $this->assertEquals('foo', $this->validator->getName());
    }

    public function testTimeoutHasDefaultValue(): void
    {
        $this->assertEquals(300, $this->validator->getTimeout());
    }

    /**
     * @return (int|null|string)[][]
     *
     * @psalm-return array<array-key, array{0: null|int|string, 1: null|int}>
     */
    public function timeoutValuesDataProvider(): array
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
     * @dataProvider timeoutValuesDataProvider
     *
     * @return void
     */
    public function testTimeoutIsMutable($timeout, $expected): void
    {
        $this->validator->setTimeout($timeout);
        $this->assertEquals($expected, $this->validator->getTimeout());
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
            if ($key == 'session') {
                $this->assertSame($container, $value);
                continue;
            }
            $method = 'get' . $key;
            $this->assertEquals($value, $validator->$method());
        }
    }

    public function testHashIsGeneratedOnFirstRetrieval(): void
    {
        $hash = $this->validator->getHash();
        $this->assertNotEmpty($hash);
        $test = $this->validator->getHash();
        $this->assertEquals($hash, $test);
    }

    public function testSessionNameIsDerivedFromClassSaltAndName(): void
    {
        $class = get_class($this->validator);
        $class = str_replace('\\', '_', $class);
        $expected = sprintf('%s_%s_%s', $class, $this->validator->getSalt(), $this->validator->getName());
        $this->assertEquals($expected, $this->validator->getSessionName());
    }

    public function testSessionNameRemainsValidForElementBelongingToFieldset(): void
    {
        $this->validator->setName('fieldset[csrf]');
        $class = get_class($this->validator);
        $class = str_replace('\\', '_', $class);
        $name = strtr($this->validator->getName(), ['[' => '_', ']' => '']);
        $expected = sprintf('%s_%s_%s', $class, $this->validator->getSalt(), $name);
        $this->assertEquals($expected, $this->validator->getSessionName());
    }

    public function testIsValidReturnsFalseWhenValueDoesNotMatchHash(): void
    {
        $this->assertFalse($this->validator->isValid('foo'));
    }

    public function testValidationErrorMatchesNotSameConstantAndRelatedMessage(): void
    {
        $this->validator->isValid('foo');
        $messages = $this->validator->getMessages();
        $this->assertArrayHasKey(Csrf::NOT_SAME, $messages);
        $this->assertEquals('The form submitted did not originate from the expected site', $messages[Csrf::NOT_SAME]);
    }

    public function testIsValidReturnsTrueWhenValueMatchesHash(): void
    {
        $hash = $this->validator->getHash();
        $this->assertTrue($this->validator->isValid($hash));
    }

    public function testSessionContainerContainsHashAfterHashHasBeenGenerated(): void
    {
        $hash        = $this->validator->getHash();
        $container   = $this->validator->getSession();
        $test        = $container->hash; // Doing this, as expiration hops are 1; have to grab on first access
        $this->assertEquals($hash, $test);
    }

    public function testSettingNewSessionContainerSetsHashInNewContainer(): void
    {
        $hash        = $this->validator->getHash();
        $container   = new Container('foo', $this->sessionManager);
        $this->validator->setSession($container);
        $test        = $container->hash; // Doing this, as expiration hops are 1; have to grab on first access
        $this->assertEquals($hash, $test);
    }

    public function testMultipleValidatorsSharingContainerGenerateDifferentHashes(): void
    {
        $validatorOne = new Csrf();
        $validatorTwo = new Csrf();

        $containerOne = $validatorOne->getSession();
        $containerTwo = $validatorOne->getSession();

        $this->assertSame($containerOne, $containerTwo);

        $hashOne = $validatorOne->getHash();
        $hashTwo = $validatorTwo->getHash();
        $this->assertNotEquals($hashOne, $hashTwo);
    }

    public function testCanValidateAnyHashWithinTheSameContainer(): void
    {
        $validatorOne = new Csrf();
        $validatorTwo = new Csrf();

        $hashOne = $validatorOne->getHash();
        $hashTwo = $validatorTwo->getHash();

        $this->assertTrue($validatorOne->isValid($hashOne));
        $this->assertTrue($validatorOne->isValid($hashTwo));
        $this->assertTrue($validatorTwo->isValid($hashOne));
        $this->assertTrue($validatorTwo->isValid($hashTwo));
    }

    public function testCannotValidateHashesOfOtherContainers(): void
    {
        $validatorOne = new Csrf();
        $validatorTwo = new Csrf(['name' => 'foo']);

        $containerOne = $validatorOne->getSession();
        $containerTwo = $validatorTwo->getSession();

        $this->assertNotSame($containerOne, $containerTwo);

        $hashOne = $validatorOne->getHash();
        $hashTwo = $validatorTwo->getHash();

        $this->assertTrue($validatorOne->isValid($hashOne));
        $this->assertFalse($validatorOne->isValid($hashTwo));
        $this->assertFalse($validatorTwo->isValid($hashOne));
        $this->assertTrue($validatorTwo->isValid($hashTwo));
    }

    public function testCannotReValidateAnExpiredHash(): void
    {
        $hash = $this->validator->getHash();

        $this->assertTrue($this->validator->isValid($hash));

        $this->sessionManager->getStorage()->setMetadata(
            $this->validator->getSession()->getName(),
            ['EXPIRE' => $_SERVER['REQUEST_TIME'] - 18600]
        );

        $this->assertFalse($this->validator->isValid($hash));
    }

    public function testCanValidateHasheWithoutId(): void
    {
        $method = new \ReflectionMethod(get_class($this->validator), 'getTokenFromHash');
        $method->setAccessible(true);

        $hash = $this->validator->getHash();
        $bareToken = $method->invoke($this->validator, $hash);

        $this->assertTrue($this->validator->isValid($bareToken));
    }

    public function testCanRejectArrayValues(): void
    {
        $this->assertFalse($this->validator->isValid([]));
    }

    /**
     * @return string[][]
     *
     * @psalm-return array<array-key, array{0: string}>
     */
    public function fakeValuesDataProvider(): array
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

    /**
     * @dataProvider fakeValuesDataProvider
     *
     * @return void
     */
    public function testWithFakeValues($value): void
    {
        $validator = new Csrf();
        $this->assertFalse($validator->isValid($value));
    }
}
