<?php

declare(strict_types=1);

namespace LaminasTest\Validator;

use Laminas\Validator\Identical;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

final class IdenticalTest extends TestCase
{
    public function testValidatingWhenTokenNullReturnsFalse(): void
    {
        $validator = new Identical();
        self::assertFalse($validator->isValid('foo'));
        self::assertArrayHasKey(Identical::MISSING_TOKEN, $validator->getMessages());
    }

    public function testValidatingAgainstTokenWithNonMatchingValueReturnsFalse(): void
    {
        $validator = new Identical(['token' => 'foo']);
        self::assertFalse($validator->isValid('bar'));
        self::assertArrayHasKey(Identical::NOT_SAME, $validator->getMessages());
    }

    public function testValidatingAgainstTokenWithMatchingValueReturnsTrue(): void
    {
        $validator = new Identical(['token' => 'foo']);
        self::assertTrue($validator->isValid('foo'));
    }

    #[Group('Laminas-6953')]
    public function testValidatingAgainstEmptyToken(): void
    {
        $validator = new Identical(['token' => '']);
        self::assertTrue($validator->isValid(''));
    }

    #[Group('Laminas-7128')]
    public function testValidatingAgainstNonStrings(): void
    {
        $validator = new Identical(['token' => true]);
        self::assertTrue($validator->isValid(true));
        self::assertFalse($validator->isValid(1));

        $validator = new Identical(['token' => ['one' => 'two', 'three']]);

        self::assertTrue($validator->isValid(['one' => 'two', 'three']));
        self::assertFalse($validator->isValid([]));
    }

    public function testValidatingTokenArray(): void
    {
        $validator = new Identical(['token' => 123]);

        self::assertTrue($validator->isValid(123));
        self::assertFalse($validator->isValid(['token' => 123]));
    }

    public function testValidatingNonStrictToken(): void
    {
        $validator = new Identical(['token' => 123, 'strict' => false]);
        self::assertTrue($validator->isValid('123'));

        $validator = new Identical(['token' => 123, 'strict' => true]);
        self::assertFalse($validator->isValid('123'));
    }

    public function testValidatingStringTokenInContext(): void
    {
        $validator = new Identical(['token' => 'email']);

        self::assertTrue($validator->isValid(
            'john@doe.com',
            ['email' => 'john@doe.com']
        ));

        self::assertFalse($validator->isValid(
            'john@doe.com',
            ['email' => 'harry@hoe.com']
        ));

        self::assertFalse($validator->isValid(
            'harry@hoe.com',
            ['email' => 'john@doe.com']
        ));
    }

    public function testValidatingArrayTokenInContext(): void
    {
        $validator = new Identical(['token' => ['user' => 'email']]);

        self::assertTrue($validator->isValid(
            'john@doe.com',
            [
                'user' => [
                    'email' => 'john@doe.com',
                ],
            ]
        ));

        self::assertFalse($validator->isValid(
            'john@doe.com',
            [
                'user' => [
                    'email' => 'harry@hoe.com',
                ],
            ]
        ));

        self::assertFalse($validator->isValid(
            'harry@hoe.com',
            [
                'user' => [
                    'email' => 'john@doe.com',
                ],
            ]
        ));
    }

    public function testLiteralParameterDoesNotAffectValidationWhenNoContextIsProvided(): void
    {
        $validator = new Identical([
            'token'   => ['foo' => 'bar'],
            'literal' => false,
        ]);

        self::assertTrue($validator->isValid(['foo' => 'bar']));

        $validator = new Identical([
            'token'   => ['foo' => 'bar'],
            'literal' => true,
        ]);

        self::assertTrue($validator->isValid(['foo' => 'bar']));
    }

    public function testLiteralParameterWorksWhenContextIsProvided(): void
    {
        $validator = new Identical([
            'token'   => ['foo' => 'bar'],
            'literal' => true,
        ]);

        self::assertTrue($validator->isValid(
            ['foo' => 'bar'],
            ['foo' => 'baz'] // Provide a context to make sure the literal parameter will work
        ));
    }
}
