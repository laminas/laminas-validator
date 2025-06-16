<?php

declare(strict_types=1);

namespace LaminasTest\Validator;

use AssertionError;
use Exception;
use Laminas\Validator\Callback;
use Laminas\Validator\Exception\InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Throwable;

use function assert;

final class CallbackTest extends TestCase
{
    public function testBasic(): void
    {
        $validator = new Callback(
            static fn (mixed $value): bool => $value === 'test',
        );

        self::assertTrue($validator->isValid('test'));
    }

    /** @return array<string, array{0: array<never, never>|null}> */
    public static function emptyContextProvider(): array
    {
        return [
            'empty-array' => [[]],
            'null'        => [null],
        ];
    }

    /** @param array<string, mixed>|null $context */
    #[DataProvider('emptyContextProvider')]
    public function testOptionsArePassedAsThirdArgumentWhenNoContextIsPresent(?array $context): void
    {
        $validator = new Callback([
            'throwExceptions' => true,
            'callback'        => static function (mixed $value, array $context, string $foo): bool {
                self::assertSame([], $context);

                return $value === 'test' && $foo === 'foo';
            },
            'callbackOptions' => ['foo' => 'foo'],
        ]);

        self::assertTrue($validator->isValid('test', $context));
    }

    public function testOptionsArePassedAsThirdArgumentWhenContextIsNonEmpty(): void
    {
        $givenContext = ['baz' => 'bat'];
        $validator    = new Callback([
            'throwExceptions' => true,
            'callback'        => static fn(mixed $value, array $context, string $foo): bool
                => $value === 'test' && $foo === 'foo' && $context === $givenContext,
            'callbackOptions' => ['foo' => 'foo'],
        ]);

        self::assertTrue($validator->isValid('test', $givenContext));
    }

    public function testContextIsSecondArgumentWhenGiven(): void
    {
        $givenContext = ['baz' => 'bat'];
        $validator    = new Callback([
            'throwExceptions' => true,
            'callback'        => static fn(mixed $value, array $context): bool
                => $value === 'test' && $context === $givenContext,
            'callbackOptions' => [],
        ]);

        self::assertTrue($validator->isValid('test', $givenContext));
    }

    public function testInvalidCallback(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('A callable must be provided');

        /** @psalm-suppress InvalidArgument */
        new Callback([$this, 'noMethodExists']);
    }

    public function testThatExceptionsRaisedInsideTheCallbackAreCaughtByDefault(): void
    {
        $exception = new Exception('Foo');
        $validator = new Callback(static function () use ($exception): bool {
            throw $exception;
        });

        self::assertFalse($validator->isValid('whatever'));
        self::assertArrayHasKey(Callback::INVALID_CALLBACK, $validator->getMessages());
    }

    public function testThatCallbackExceptionsCanBeAllowedToPropagate(): void
    {
        $exception = new Exception('Callback Exception');
        $validator = new Callback(static function () use ($exception): bool {
            throw $exception;
        });

        try {
            $validator->isValid('whatever');
            self::fail('An exception should have been thrown');
        } catch (Throwable) {
            self::assertSame('Callback Exception', $exception->getMessage());
        }

        self::assertArrayHasKey(Callback::INVALID_CALLBACK, $validator->getMessages());
    }

    public function testThatCallbacksCanBeBoundToTheValidator(): void
    {
        $callback = function (mixed $value): bool {
            /** @psalm-suppress TypeDoesNotContainType Psalm is not aware this function will be bound to the validator */
            assert($this instanceof Callback);

            if ($value === 'a') {
                $this->setMessage('Bad News 1', Callback::INVALID_VALUE);

                return false;
            }

            if ($value === 'b') {
                $this->setMessage('Bad News 2', Callback::INVALID_VALUE);

                return false;
            }

            return true;
        };

        $validator = new Callback([
            'callback' => $callback,
            'bind'     => true,
        ]);

        self::assertFalse($validator->isValid('a'));
        self::assertSame('Bad News 1', $validator->getMessages()[Callback::INVALID_VALUE] ?? null);
        self::assertFalse($validator->isValid('b'));
        self::assertSame('Bad News 2', $validator->getMessages()[Callback::INVALID_VALUE] ?? null);
    }

    public function testThatCallbacksAreNotBoundToTheValidatorByDefault(): void
    {
        $callback = function (mixed $value): bool {
            /** @psalm-suppress TypeDoesNotContainType Psalm is correct here! */
            assert($this instanceof Callback);

            return $value === 'foo';
        };

        $validator = new Callback([
            'callback'        => $callback,
            'throwExceptions' => true,
        ]);

        $this->expectException(AssertionError::class);

        $validator->isValid('foo');
    }
}
