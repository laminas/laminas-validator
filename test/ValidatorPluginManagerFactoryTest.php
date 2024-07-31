<?php

declare(strict_types=1);

namespace LaminasTest\Validator;

use Laminas\Validator\Digits;
use Laminas\Validator\ValidatorInterface;
use Laminas\Validator\ValidatorPluginManager;
use Laminas\Validator\ValidatorPluginManagerFactory;
use LaminasTest\Validator\TestAsset\InMemoryContainer;
use PHPUnit\Framework\TestCase;

final class ValidatorPluginManagerFactoryTest extends TestCase
{
    public function testFactoryReturnsPluginManager(): void
    {
        $factory    = new ValidatorPluginManagerFactory();
        $validators = $factory(new InMemoryContainer());

        self::assertInstanceOf(ValidatorPluginManager::class, $validators);
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
                    'test-too' => static fn(): ValidatorInterface => $validator,
                ],
            ],
        ];

        $container = new InMemoryContainer();
        $container->set('config', $config);

        $factory    = new ValidatorPluginManagerFactory();
        $validators = $factory($container);

        self::assertInstanceOf(ValidatorPluginManager::class, $validators);
        self::assertTrue($validators->has('test'));
        self::assertInstanceOf(Digits::class, $validators->get('test'));
        self::assertTrue($validators->has('test-too'));
        self::assertSame($validator, $validators->get('test-too'));
    }

    public function testDoesNotConfigureValidatorServicesWhenConfigServiceNotPresent(): void
    {
        $container  = new InMemoryContainer();
        $factory    = new ValidatorPluginManagerFactory();
        $validators = $factory($container);

        self::assertInstanceOf(ValidatorPluginManager::class, $validators);
    }

    public function testDoesNotConfigureValidatorServicesWhenConfigServiceDoesNotContainValidatorsConfig(): void
    {
        $container = new InMemoryContainer();
        $container->set('config', ['foo' => 'bar']);
        $factory    = new ValidatorPluginManagerFactory();
        $validators = $factory($container);

        self::assertInstanceOf(ValidatorPluginManager::class, $validators);
        self::assertFalse($validators->has('foo'));
    }
}
