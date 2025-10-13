<?php

declare(strict_types=1);

namespace LaminasTest\Validator;

use Laminas\ServiceManager\ServiceManager;
use Laminas\Validator\ConfigProvider;
use Laminas\Validator\NotEmpty;
use Laminas\Validator\ValidatorChain;
use Laminas\Validator\ValidatorPluginManager;
use PHPUnit\Framework\TestCase;

final class ValidatorChainInvokableFactoryTest extends TestCase
{
    private ValidatorPluginManager $pluginManager;

    protected function setUp(): void
    {
        parent::setUp();

        $config           = (new ConfigProvider())->__invoke();
        $deps             = $config['dependencies'];
        $deps['services'] = ['config' => $config];
        $serviceManager   = new ServiceManager($deps);

        $this->pluginManager = $serviceManager->get(ValidatorPluginManager::class);
    }

    public function testAnEmptyChainCanBeRetrievedFromThePluginManager(): void
    {
        $chain = $this->pluginManager->get(ValidatorChain::class);
        self::assertInstanceOf(ValidatorChain::class, $chain);
        self::assertCount(0, $chain);
        self::assertSame($this->pluginManager, $chain->getPluginManager());
    }

    public function testAnEmptyChainCanBeBuiltByThePluginManager(): void
    {
        $chain = $this->pluginManager->build(ValidatorChain::class, []);
        self::assertInstanceOf(ValidatorChain::class, $chain);
        self::assertCount(0, $chain);
        self::assertSame($this->pluginManager, $chain->getPluginManager());
    }

    public function testANonEmptyChainCanBeBuiltByThePluginManager(): void
    {
        $chain = $this->pluginManager->build(ValidatorChain::class, [
            'notEmpty' => [
                'name'    => NotEmpty::class,
                'options' => [
                    'messages' => [
                        NotEmpty::IS_EMPTY => 'Bad News',
                    ],
                ],
            ],
        ]);

        self::assertInstanceOf(ValidatorChain::class, $chain);
        self::assertCount(1, $chain);

        self::assertFalse($chain->isValid(''));
        self::assertSame([
            NotEmpty::IS_EMPTY => 'Bad News',
        ], $chain->getMessages());
    }
}
