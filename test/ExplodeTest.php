<?php

declare(strict_types=1);

namespace LaminasTest\Validator;

use Laminas\ServiceManager\Factory\InvokableFactory;
use Laminas\ServiceManager\ServiceManager;
use Laminas\Validator\Callback;
use Laminas\Validator\Exception\RuntimeException;
use Laminas\Validator\Explode;
use Laminas\Validator\InArray;
use Laminas\Validator\NotEmpty;
use Laminas\Validator\ValidatorPluginManager;
use PHPUnit\Framework\TestCase;

final class ExplodeTest extends TestCase
{
    public function testRaisesExceptionWhenValidatorIsMissing(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('expects a validator to be set');

        new Explode();
    }

    public function testThatNonStringsCannotBeSplit(): void
    {
        $validator = new Explode([
            'validator' => new NotEmpty(),
        ]);

        self::assertFalse($validator->isValid(1));
        self::assertArrayHasKey(Explode::INVALID, $validator->getMessages());
    }

    public function testNonDefaultSeparator(): void
    {
        $validator = new Explode([
            'validator'      => new NotEmpty(),
            'valueDelimiter' => ';',
        ]);

        self::assertTrue($validator->isValid('a;b;c'));
        self::assertFalse($validator->isValid('a;;b'));
    }

    public function testValidatorOptionCanBeAValidatorSpecification(): void
    {
        $validator = new Explode([
            'validator' => [
                'name'    => InArray::class,
                'options' => [
                    'haystack' => ['a', 'b', 'c'],
                ],
            ],
        ]);

        self::assertTrue($validator->isValid('a,b,c'));
    }

    public function testInjectedPluginManagerIsUsed(): void
    {
        $plugins = new ValidatorPluginManager(new ServiceManager(), [
            'factories' => [
                InArray::class => InvokableFactory::class,
            ],
            'aliases'   => [
                'foo' => InArray::class,
            ],
        ]);

        $validator = new Explode([
            'validator'              => [
                'name'    => 'foo',
                'options' => [
                    'haystack' => ['a', 'b', 'c'],
                ],
            ],
            'validatorPluginManager' => $plugins,
        ]);

        self::assertTrue($validator->isValid('a,b,c'));
    }

    public function testValidatorSpecificationWithMissingNameIsExceptional(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Invalid validator specification');

        /** @psalm-suppress InvalidArgument */
        new Explode([
            'validator' => [
                'options' => ['foo'],
            ],
        ]);
    }

    public function testValidatorOptionCanBeAKnownAlias(): void
    {
        $validator = new Explode([
            'validator' => NotEmpty::class,
        ]);
        self::assertTrue($validator->isValid('a,b,c'));
    }

    public function testGetMessagesMultipleInvalid(): void
    {
        $validator = new Explode([
            'validator' => new InArray([
                'haystack' => ['a', 'b', 'c'],
            ]),
        ]);

        $messages = [
            Explode::INVALID_ITEM => '3 items were invalid: The input was not found in the haystack',
        ];

        self::assertFalse($validator->isValid('x,y,z'));
        self::assertSame($messages, $validator->getMessages());
    }

    public function testGetMessagesWhenBreakIsTrue(): void
    {
        $validator = new Explode([
            'validator'           => new InArray([
                'haystack' => ['a', 'b', 'c'],
            ]),
            'breakOnFirstFailure' => true,
        ]);

        $messages = [
            Explode::INVALID_ITEM => '1 items were invalid: The input was not found in the haystack',
        ];

        self::assertFalse($validator->isValid('x,y,z'));
        self::assertSame($messages, $validator->getMessages());
    }

    public function testContextIsPassedToComposedValidator(): void
    {
        $context   = ['foo' => 'bar'];
        $validator = new Explode([
            'validator'           => new Callback(static function (mixed $v, array $c) use ($context): bool {
                self::assertSame($context, $c);

                return true;
            }),
            'valueDelimiter'      => ',',
            'breakOnFirstFailure' => false,
        ]);

        self::assertTrue($validator->isValid('a,b,c', $context));
    }
}
