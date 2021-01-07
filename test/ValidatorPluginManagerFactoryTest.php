<?php

/**
 * @see       https://github.com/laminas/laminas-validator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-validator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-validator/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Validator;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\Validator\Digits;
use Laminas\Validator\ValidatorInterface;
use Laminas\Validator\ValidatorPluginManager;
use Laminas\Validator\ValidatorPluginManagerFactory;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class ValidatorPluginManagerFactoryTest extends TestCase
{
    use ProphecyTrait;
    public function testFactoryReturnsPluginManager(): void
    {
        $container = $this->prophesize(ContainerInterface::class)->reveal();
        $factory = new ValidatorPluginManagerFactory();

        $validators = $factory($container, ValidatorPluginManagerFactory::class);
        $this->assertInstanceOf(ValidatorPluginManager::class, $validators);
    }

    /**
     * @depends testFactoryReturnsPluginManager
     *
     * @return void
     */
    public function testFactoryConfiguresPluginManagerUnderContainerInterop(): void
    {
        $container = $this->prophesize(ContainerInterface::class)->reveal();
        $validator = $this->prophesize(ValidatorInterface::class)->reveal();

        $factory = new ValidatorPluginManagerFactory();
        $validators = $factory($container, ValidatorPluginManagerFactory::class, [
            'services' => [
                'test' => $validator,
            ],
        ]);
        $this->assertSame($validator, $validators->get('test'));
    }

    /**
     * @depends testFactoryReturnsPluginManager
     *
     * @return void
     */
    public function testFactoryConfiguresPluginManagerUnderServiceManagerV2(): void
    {
        $container = $this->prophesize(ServiceLocatorInterface::class);
        $container->willImplement(ContainerInterface::class);

        $validator = $this->prophesize(ValidatorInterface::class)->reveal();

        $factory = new ValidatorPluginManagerFactory();
        $factory->setCreationOptions([
            'services' => [
                'test' => $validator,
            ],
        ]);

        $validators = $factory->createService($container->reveal());
        $this->assertSame($validator, $validators->get('test'));
    }

    public function testConfiguresValidatorServicesWhenFound(): void
    {
        $validator = $this->prophesize(ValidatorInterface::class)->reveal();
        $config = [
            'validators' => [
                'aliases' => [
                    'test' => Digits::class,
                ],
                'factories' => [
                    'test-too' => function ($container) use ($validator): \Laminas\Validator\ValidatorInterface {
                        return $validator;
                    },
                ],
            ],
        ];

        $container = $this->prophesize(ServiceLocatorInterface::class);
        $container->willImplement(ContainerInterface::class);

        $container->has('ServiceListener')->willReturn(false);
        $container->has('config')->willReturn(true);
        $container->get('config')->willReturn($config);
        $container->has('MvcTranslator')->willReturn(false); // necessary due to default initializers

        $factory = new ValidatorPluginManagerFactory();
        $validators = $factory($container->reveal(), 'ValidatorManager');

        $this->assertInstanceOf(ValidatorPluginManager::class, $validators);
        $this->assertTrue($validators->has('test'));
        $this->assertInstanceOf(Digits::class, $validators->get('test'));
        $this->assertTrue($validators->has('test-too'));
        $this->assertSame($validator, $validators->get('test-too'));
    }

    public function testDoesNotConfigureValidatorServicesWhenServiceListenerPresent(): void
    {
        $validator = $this->prophesize(ValidatorInterface::class)->reveal();
        $config = [
            'validators' => [
                'aliases' => [
                    'test' => Digits::class,
                ],
                'factories' => [
                    'test-too' => function ($container) use ($validator): \Laminas\Validator\ValidatorInterface {
                        return $validator;
                    },
                ],
            ],
        ];

        $container = $this->prophesize(ServiceLocatorInterface::class);
        $container->willImplement(ContainerInterface::class);

        $container->has('ServiceListener')->willReturn(true);
        $container->has('config')->shouldNotBeCalled();
        $container->get('config')->shouldNotBeCalled();
        $container->has('MvcTranslator')->willReturn(false); // necessary due to default initializers

        $factory = new ValidatorPluginManagerFactory();
        $validators = $factory($container->reveal(), 'ValidatorManager');

        $this->assertInstanceOf(ValidatorPluginManager::class, $validators);
        $this->assertFalse($validators->has('test'));
        $this->assertFalse($validators->has('test-too'));
    }

    public function testDoesNotConfigureValidatorServicesWhenConfigServiceNotPresent(): void
    {
        $container = $this->prophesize(ServiceLocatorInterface::class);
        $container->willImplement(ContainerInterface::class);

        $container->has('ServiceListener')->willReturn(false);
        $container->has('config')->willReturn(false);
        $container->get('config')->shouldNotBeCalled();
        $container->has('MvcTranslator')->willReturn(false); // necessary due to default initializers

        $factory = new ValidatorPluginManagerFactory();
        $validators = $factory($container->reveal(), 'ValidatorManager');

        $this->assertInstanceOf(ValidatorPluginManager::class, $validators);
    }

    public function testDoesNotConfigureValidatorServicesWhenConfigServiceDoesNotContainValidatorsConfig(): void
    {
        $container = $this->prophesize(ServiceLocatorInterface::class);
        $container->willImplement(ContainerInterface::class);

        $container->has('ServiceListener')->willReturn(false);
        $container->has('config')->willReturn(true);
        $container->get('config')->willReturn(['foo' => 'bar']);
        $container->has('MvcTranslator')->willReturn(false); // necessary due to default initializers

        $factory = new ValidatorPluginManagerFactory();
        $validators = $factory($container->reveal(), 'ValidatorManager');

        $this->assertInstanceOf(ValidatorPluginManager::class, $validators);
        $this->assertFalse($validators->has('foo'));
    }
}
