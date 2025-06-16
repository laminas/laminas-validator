<?php

declare(strict_types=1);

namespace LaminasTest\Validator;

use Laminas\ServiceManager\ServiceManager;
use Laminas\Validator\ConditionalFactory;
use Laminas\Validator\NotEmpty;
use Laminas\Validator\ValidatorChainFactory;
use Laminas\Validator\ValidatorPluginManager;
use LaminasTest\Validator\TestAsset\InMemoryContainer;
use PHPUnit\Framework\TestCase;

final class ConditionalFactoryTest extends TestCase
{
    public function testThatOptionsArePassedToTheValidator(): void
    {
        $chainFactory = new ValidatorChainFactory(new ValidatorPluginManager(new ServiceManager()));

        $container = new InMemoryContainer();
        $container->set(ValidatorChainFactory::class, $chainFactory);

        $factory   = new ConditionalFactory();
        $validator = $factory->__invoke($container, 'Whatever', [
            'rule'       => static fn(array $context): bool => ($context['trigger'] ?? null) === true,
            'validators' => [
                ['name' => NotEmpty::class],
            ],
        ]);

        self::assertTrue($validator->isValid('Foo', ['trigger' => true]));
        self::assertFalse($validator->isValid('', ['trigger' => true]));
        self::assertTrue($validator->isValid('', ['trigger' => false]));
    }
}
