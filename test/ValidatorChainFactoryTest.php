<?php

declare(strict_types=1);

namespace LaminasTest\Validator;

use Laminas\ServiceManager\ServiceManager;
use Laminas\Validator\Exception\InvalidArgumentException;
use Laminas\Validator\NotEmpty;
use Laminas\Validator\StringLength;
use Laminas\Validator\ValidatorChainFactory;
use Laminas\Validator\ValidatorPluginManager;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Container\NotFoundExceptionInterface;

use function iterator_to_array;

final class ValidatorChainFactoryTest extends TestCase
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

    public function testSpecificationsCanBeMixedWithInstances(): void
    {
        $stringLength = new StringLength(['min' => 5]);
        $chain        = $this->factory->fromArray([
            [
                'name'                   => NotEmpty::class,
                'priority'               => 10,
                'break_chain_on_failure' => true,
            ],
            $stringLength,
        ]);

        $validators = iterator_to_array($chain, false);
        self::assertCount(2, $chain);
        self::assertInstanceOf(NotEmpty::class, $validators[0]['instance']);
        self::assertSame($stringLength, $validators[1]['instance']);
    }

    public function testSpecificationsCanBeAllInstances(): void
    {
        $stringLength = new StringLength(['min' => 5]);
        $notEmpty     = new NotEmpty();
        $chain        = $this->factory->fromArray([
            $notEmpty,
            $stringLength,
        ]);

        $validators = iterator_to_array($chain, false);
        self::assertCount(2, $chain);
        self::assertSame($notEmpty, $validators[0]['instance']);
        self::assertSame($stringLength, $validators[1]['instance']);
    }

    /** @return array<string, array{0: array, 1: string}> */
    public static function invalidSpecProvider(): array
    {
        return [
            'Null'                 => [
                [null],
                'Validator specifications must be an array',
            ],
            'String'               => [
                ['foo'],
                'Validator specifications must be an array',
            ],
            'Missing Name'         => [
                [['priority' => 10]],
                'Validator specifications should have a `name` key',
            ],
            'Non-int Priority'     => [
                [['name' => 'foo', 'priority' => 'a']],
                'Validator priorities must be integers',
            ],
            'Non-array options'    => [
                [['name' => 'foo', 'options' => 'a']],
                'Validator options should be arrays',
            ],
            'Non-bool break chain' => [
                [['name' => 'foo', 'break_chain_on_failure' => 'a']],
                'The `break_chain_on_failure` key must contain a boolean when set',
            ],
        ];
    }

    /** @param mixed[] $spec */
    #[DataProvider('invalidSpecProvider')]
    public function testInvalidSpecsWillCauseExceptions(array $spec, string $expectMessage): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($expectMessage);
        $this->factory->fromArray($spec);
    }
}
