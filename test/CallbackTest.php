<?php

declare(strict_types=1);

namespace LaminasTest\Validator;

use Exception;
use Laminas\Validator\Callback;
use Laminas\Validator\Exception\InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Throwable;

use function array_keys;
use function func_get_args;

final class CallbackTest extends TestCase
{
    /**
     * Ensures that the validator follows expected behavior
     */
    public function testBasic(): void
    {
        $valid = new Callback([$this, 'objectCallback']);

        self::assertTrue($valid->isValid('test'));
    }

    public function testStaticCallback(): void
    {
        $valid = new Callback(
            [self::class, 'staticCallback'],
        );

        self::assertTrue($valid->isValid('test'));
    }

    public function testSettingDefaultOptionsAfterwards(): void
    {
        $valid = new Callback([$this, 'objectCallback']);
        $valid->setCallbackOptions(['options']);

        self::assertSame(['options'], $valid->getCallbackOptions());
        self::assertTrue($valid->isValid('test'));
    }

    public function testSettingDefaultOptions(): void
    {
        $valid = new Callback(['callback' => [$this, 'objectCallback'], 'callbackOptions' => ['options']]);

        self::assertSame(['options'], $valid->getCallbackOptions());
        self::assertTrue($valid->isValid('test'));
    }

    public function testGettingCallback(): void
    {
        $valid = new Callback([$this, 'objectCallback']);

        self::assertSame([$this, 'objectCallback'], $valid->getCallback());
    }

    public function testInvalidCallback(): void
    {
        $valid = new Callback([$this, 'objectCallback']);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid callback given');

        /** @psalm-suppress UndefinedFunction */
        $valid->setCallback('invalidCallback');
    }

    public function testCallbackOptionsAreReceived(): void
    {
        $valid = new Callback(['callback' => [$this, 'optionsCallback'], 'callbackOptions' => ['options']]);

        self::assertSame(['options'], $valid->getCallbackOptions());
        self::assertTrue($valid->isValid('test', 'something'));
    }

    public function optionsCallback(): bool
    {
        $args = func_get_args();

        self::assertContains('something', $args);
        self::assertContains('options', $args);

        return true;
    }

    public function testEqualsMessageTemplates(): void
    {
        $validator = new Callback([$this, 'objectCallback']);

        self::assertSame(
            [
                Callback::INVALID_VALUE,
                Callback::INVALID_CALLBACK,
            ],
            array_keys($validator->getMessageTemplates()),
        );
        self::assertSame($validator->getOption('messageTemplates'), $validator->getMessageTemplates());
    }

    public function testCanAcceptContextWithoutOptions(): void
    {
        $value     = 'bar';
        $context   = ['foo' => 'bar', 'bar' => 'baz'];
        $validator = new Callback(static fn($v, $c): bool => ($value === $v) && ($context === $c));

        self::assertTrue($validator->isValid($value, $context));
    }

    public function testCanAcceptContextWithOptions(): void
    {
        $value     = 'bar';
        $context   = ['foo' => 'bar', 'bar' => 'baz'];
        $options   = ['baz' => 'bat'];
        $validator = new Callback(
            static fn($v, $c, $baz): bool => ($value === $v)
            && ($context === $c) && ($options['baz'] === $baz),
        );
        $validator->setCallbackOptions($options);

        self::assertTrue($validator->isValid($value, $context));
    }

    /**
     * @return true
     */
    public function objectCallback(): bool
    {
        return true;
    }

    /**
     * @return true
     */
    public static function staticCallback(): bool
    {
        return true;
    }

    public function testIsValidRaisesExceptionWhenNoCallbackPresent(): void
    {
        $validator = new Callback();
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('No callback given');

        $validator->isValid('test');
    }

    public function testThatExceptionsRaisedInsideTheCallbackAreCaught(): void
    {
        $validator = new Callback();
        $exception = new Exception('Foo');
        $validator->setCallback(static function () use ($exception): never {
            throw $exception;
        });

        self::assertFalse($validator->isValid('whatever'));
        self::assertArrayHasKey(Callback::INVALID_CALLBACK, $validator->getMessages());
    }

    public function testThatCallbackExceptionsCanBeAllowedToPropagate(): void
    {
        $validator = new Callback();
        $exception = new Exception('Callback Exception');
        $validator->setCallback(static function () use ($exception): never {
            throw $exception;
        });

        try {
            $validator->isValid('whatever');
            self::fail('An exception should have been thrown');
        } catch (Throwable $error) {
            self::assertSame('Callback Exception', $exception->getMessage());
        }

        self::assertArrayHasKey(Callback::INVALID_CALLBACK, $validator->getMessages());
    }

    public function testThatCallbacksCanBeBoundToTheValidatorInstance(): void
    {
        $closure = function (): bool {
            /** @var Callback $this */
            $this->setMessage('Custom Error Message', Callback::INVALID_VALUE);

            return false;
        };

        $validator = new Callback([
            'callback' => $closure,
            'bind'     => true,
        ]);

        self::assertFalse($validator->isValid('anything'));
        $messages = $validator->getMessages();
        self::assertArrayHasKey(Callback::INVALID_VALUE, $messages);
        self::assertSame('Custom Error Message', $messages[Callback::INVALID_VALUE]);
    }
}
