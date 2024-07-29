<?php

declare(strict_types=1);

namespace LaminasTest\Validator;

use Laminas\ServiceManager\ServiceManager;
use Laminas\Validator\NotEmpty;
use Laminas\Validator\StringLength;
use Laminas\Validator\ValidatorChainFactory;
use Laminas\Validator\ValidatorPluginManager;
use PHPUnit\Framework\TestCase;
use Psr\Container\NotFoundExceptionInterface;

class ValidatorChainFactoryTest extends TestCase
{
    private ValidatorChainFactory $factory;

    protected function setUp(): void
    {
        $this->factory = new ValidatorChainFactory(new ValidatorPluginManager(new ServiceManager()));
    }

    public function testBasicChain(): void
    {
        $chain = $this->factory->fromArray([
            'notEmpty'     => [
                'name' => NotEmpty::class,
            ],
            'stringLength' => [
                'name'    => StringLength::class,
                'options' => [
                    'min' => 5,
                ],
            ],
        ]);

        self::assertFalse($chain->isValid(''));
        $messages = $chain->getMessages();
        self::assertArrayHasKey(NotEmpty::IS_EMPTY, $messages);
        self::assertArrayHasKey(StringLength::TOO_SHORT, $messages);
    }

    public function testValidatorAliasesCanBeUsed(): void
    {
        $chain = $this->factory->fromArray([
            'notEmpty'     => [
                'name' => 'NotEmpty',
            ],
            'stringLength' => [
                'name'    => 'StringLength',
                'options' => [
                    'min' => 5,
                ],
            ],
        ]);

        self::assertFalse($chain->isValid(''));
        $messages = $chain->getMessages();
        self::assertArrayHasKey(NotEmpty::IS_EMPTY, $messages);
        self::assertArrayHasKey(StringLength::TOO_SHORT, $messages);
    }

    public function testCreatedChainRespectsBreakChainOption(): void
    {
        $chain = $this->factory->fromArray([
            'notEmpty'     => [
                'name'                   => NotEmpty::class,
                'break_chain_on_failure' => true,
            ],
            'stringLength' => [
                'name'    => StringLength::class,
                'options' => [
                    'min' => 5,
                ],
            ],
        ]);

        self::assertFalse($chain->isValid(''));
        $messages = $chain->getMessages();
        self::assertArrayHasKey(NotEmpty::IS_EMPTY, $messages);
        self::assertArrayNotHasKey(StringLength::TOO_SHORT, $messages);
    }

    public function testCreatedChainRespectsPriorityOption(): void
    {
        $chain = $this->factory->fromArray([
            'notEmpty'     => [
                'name'                   => NotEmpty::class,
                'priority'               => 10,
                'break_chain_on_failure' => true,
            ],
            'stringLength' => [
                'name'                   => StringLength::class,
                'options'                => [
                    'min' => 5,
                ],
                'break_chain_on_failure' => true,
                'priority'               => 20,
            ],
        ]);

        self::assertFalse($chain->isValid(''));
        $messages = $chain->getMessages();
        self::assertArrayNotHasKey(NotEmpty::IS_EMPTY, $messages);
        self::assertArrayHasKey(StringLength::TOO_SHORT, $messages);
    }

    public function testPsrContainerNotFoundIsThrownForInvalidServiceNames(): void
    {
        $this->expectException(NotFoundExceptionInterface::class);
        $this->factory->fromArray([
            'invalid' => [
                'name' => 'Unknown',
            ],
        ]);
    }
}
