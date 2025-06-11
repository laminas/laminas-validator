<?php

declare(strict_types=1);

namespace LaminasTest\Validator;

use Laminas\ServiceManager\ServiceManager;
use Laminas\Validator\Conditional;
use Laminas\Validator\ConfigProvider;
use Laminas\Validator\Digits;
use Laminas\Validator\Exception\InvalidArgumentException;
use Laminas\Validator\NotEmpty;
use Laminas\Validator\ValidatorChainFactory;
use Laminas\Validator\ValidatorPluginManager;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

final class ConditionalTest extends TestCase
{
    private ValidatorChainFactory $factory;

    protected function setUp(): void
    {
        $this->factory = new ValidatorChainFactory(new ValidatorPluginManager(new ServiceManager()));
    }

    public function testConditionalValidationHappyPath(): void
    {
        $validator = new Conditional(
            $this->factory,
            [
                'rule'       => static fn(array $context): bool => ($context['trigger'] ?? null) === true,
                'validators' => [
                    ['name' => NotEmpty::class],
                ],
            ],
        );

        self::assertTrue(
            $validator->isValid(''),
            'When the rule returns false, validation should pass for any input',
        );

        self::assertTrue(
            $validator->isValid('Foo', ['trigger' => true]),
            'When the rule returns true, validation should execute for the given valid value and pass',
        );

        self::assertFalse(
            $validator->isValid('', ['trigger' => true]),
            'When the rule returns true, validation should execute for the given invalid value and fail',
        );
    }

    public function testMessagesFromTheValidationChainAreReturned(): void
    {
        $validator = new Conditional(
            $this->factory,
            [
                'rule'       => static fn(array $context): bool => ($context['trigger'] ?? null) === true,
                'validators' => [
                    ['name' => Digits::class],
                ],
            ],
        );

        self::assertFalse($validator->isValid('Foo', ['trigger' => true]));
        $messages = $validator->getMessages();
        self::assertCount(1, $messages);
        self::assertArrayHasKey(Digits::NOT_DIGITS, $messages);
    }

    public function testThatAMissingRuleIsAnError(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The `rule` option must be callable');
        /** @psalm-suppress InvalidArgument */
        new Conditional($this->factory, []);
    }

    public function testThatANonCallableRuleIsAnError(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The `rule` option must be callable');
        /** @psalm-suppress InvalidArgument */
        new Conditional($this->factory, ['rule' => [$this, 'notAKnownMethod']]);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testPluginManagerIntegration(): void
    {
        $container = new ServiceManager((new ConfigProvider())->getDependencyConfig());
        $plugins   = $container->get(ValidatorPluginManager::class);
        self::assertInstanceOf(ValidatorPluginManager::class, $plugins);
        $validator = $plugins->build(Conditional::class, [
            'rule'       => static fn(array $context): bool => ($context['trigger'] ?? null) === true,
            'validators' => [
                ['name' => Digits::class],
            ],
        ]);

        self::assertTrue($validator->isValid('123', ['trigger' => true]));
        self::assertFalse($validator->isValid('Foo', ['trigger' => true]));
        self::assertTrue($validator->isValid('Foo', ['trigger' => false]));
    }
}
