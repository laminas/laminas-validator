<?php

declare(strict_types=1);

namespace LaminasTest\Validator;

use Laminas\ServiceManager\ServiceManager;
use Laminas\Validator\ValidatorChainFactory;
use Laminas\Validator\ValidatorChainFactoryFactory;
use Laminas\Validator\ValidatorPluginManager;
use LaminasTest\Validator\TestAsset\InMemoryContainer;
use PHPUnit\Framework\TestCase;

class ValidatorChainFactoryFactoryTest extends TestCase
{
    public function testFactoryWillBeCreated(): void
    {
        $container = new InMemoryContainer();
        $container->set(ValidatorPluginManager::class, new ValidatorPluginManager(new ServiceManager()));

        $factory = (new ValidatorChainFactoryFactory())->__invoke($container);

        self::assertInstanceOf(ValidatorChainFactory::class, $factory);
    }
}
