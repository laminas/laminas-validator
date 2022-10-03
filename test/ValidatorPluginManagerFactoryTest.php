<?php

declare(strict_types=1);

namespace LaminasTest\Validator;

use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\Validator\Digits;
use Laminas\Validator\ValidatorInterface;
use Laminas\Validator\ValidatorPluginManager;
use Laminas\Validator\ValidatorPluginManagerFactory;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

/** @covers \Laminas\Validator\ValidatorPluginManagerFactory */
final class ValidatorPluginManagerFactoryTest extends TestCase
{
    public function testFactoryReturnsPluginManager(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $factory   = new ValidatorPluginManagerFactory();

        $validators = $factory($container, ValidatorPluginManagerFactory::class);

        self::assertInstanceOf(ValidatorPluginManager::class, $validators);
    }

    /**
     * @depends testFactoryReturnsPluginManager
     */
    public function testFactoryConfiguresPluginManagerUnderContainerInterop(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $validator = $this->createMock(ValidatorInterface::class);

        $factory    = new ValidatorPluginManagerFactory();
        $validators = $factory($container, ValidatorPluginManagerFactory::class, [
            'services' => [
                'test' => $validator,
            ],
        ]);

        self::assertSame($validator, $validators->get('test'));
    }

    /**
     * @depends testFactoryReturnsPluginManager
     */
    public function testFactoryConfiguresPluginManagerUnderServiceManagerV2(): void
    {
        $container = $this->createMock(ServiceLocatorInterface::class);
        $validator = $this->createMock(ValidatorInterface::class);

        $factory = new ValidatorPluginManagerFactory();
        $factory->setCreationOptions([
            'services' => [
                'test' => $validator,
            ],
        ]);

        $validators = $factory->createService($container);

        self::assertSame($validator, $validators->get('test'));
    }

    public function testConfiguresValidatorServicesWhenFound(): void
    {
        $validator = $this->createMock(ValidatorInterface::class);
        $config    = [
            'validators' => [
                'aliases'   => [
                    'test' => Digits::class,
                ],
                'factories' => [
                    'test-too' => static fn($container): ValidatorInterface => $validator,
                ],
            ],
        ];

        $container = $this->createMock(ServiceLocatorInterface::class);

        $container
            ->expects(self::exactly(3))
            ->method('has')
            ->withConsecutive(
                ['ServiceListener'],
                ['config'],
                ['MvcTranslator'], // necessary due to default initializers
            )
            ->willReturn(false, true, false);

        $container
            ->expects(self::once())
            ->method('get')
            ->with('config')
            ->willReturn($config);

        $factory    = new ValidatorPluginManagerFactory();
        $validators = $factory($container, 'ValidatorManager');

        self::assertInstanceOf(ValidatorPluginManager::class, $validators);
        self::assertTrue($validators->has('test'));
        self::assertInstanceOf(Digits::class, $validators->get('test'));
        self::assertTrue($validators->has('test-too'));
        self::assertSame($validator, $validators->get('test-too'));
    }

    public function testDoesNotConfigureValidatorServicesWhenServiceListenerPresent(): void
    {
        $container = $this->createMock(ServiceLocatorInterface::class);

        $container
            ->expects(self::once())
            ->method('has')
            ->with('ServiceListener')
            ->willReturn(true);

        $container
            ->expects(self::never())
            ->method('get')
            ->with('config');

        $factory    = new ValidatorPluginManagerFactory();
        $validators = $factory($container, 'ValidatorManager');

        self::assertInstanceOf(ValidatorPluginManager::class, $validators);
        self::assertFalse($validators->has('test'));
        self::assertFalse($validators->has('test-too'));
    }

    public function testDoesNotConfigureValidatorServicesWhenConfigServiceNotPresent(): void
    {
        $container = $this->createMock(ServiceLocatorInterface::class);

        $container
            ->expects(self::exactly(2))
            ->method('has')
            ->withConsecutive(
                ['ServiceListener'],
                ['config'],
            )
            ->willReturn(false, false);

        $container
            ->expects(self::never())
            ->method('get')
            ->with('config');

        $factory    = new ValidatorPluginManagerFactory();
        $validators = $factory($container, 'ValidatorManager');

        self::assertInstanceOf(ValidatorPluginManager::class, $validators);
    }

    public function testDoesNotConfigureValidatorServicesWhenConfigServiceDoesNotContainValidatorsConfig(): void
    {
        $container = $this->createMock(ServiceLocatorInterface::class);

        $container
            ->expects(self::exactly(2))
            ->method('has')
            ->withConsecutive(
                ['ServiceListener'],
                ['config'],
            )
            ->willReturn(false, true);

        $container
            ->expects(self::once())
            ->method('get')
            ->with('config')
            ->willReturn(['foo' => 'bar']);

        $factory    = new ValidatorPluginManagerFactory();
        $validators = $factory($container, 'ValidatorManager');

        self::assertInstanceOf(ValidatorPluginManager::class, $validators);
        self::assertFalse($validators->has('foo'));
    }
}
