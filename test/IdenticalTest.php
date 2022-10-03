<?php

declare(strict_types=1);

namespace LaminasTest\Validator;

use Laminas\Stdlib\Parameters;
use Laminas\Validator\Exception\InvalidArgumentException;
use Laminas\Validator\Identical;
use PHPUnit\Framework\TestCase;
use stdClass;

use function array_keys;

/**
 * @group Laminas_Validator
 * @covers \Laminas\Validator\Identical
 */
final class IdenticalTest extends TestCase
{
    private Identical $validator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = new Identical();
    }

    public function testTokenInitiallyNull(): void
    {
        self::assertNull($this->validator->getToken());
    }

    public function testCanSetToken(): void
    {
        $this->testTokenInitiallyNull();

        $this->validator->setToken('foo');

        self::assertSame('foo', $this->validator->getToken());
    }

    public function testCanSetTokenViaConstructor(): void
    {
        $validator = new Identical('foo');

        self::assertSame('foo', $validator->getToken());
    }

    public function testValidatingWhenTokenNullReturnsFalse(): void
    {
        self::assertFalse($this->validator->isValid('foo'));
    }

    public function testValidatingWhenTokenNullSetsMissingTokenMessage(): void
    {
        $this->testValidatingWhenTokenNullReturnsFalse();

        $messages = $this->validator->getMessages();

        self::assertArrayHasKey('missingToken', $messages);
    }

    public function testValidatingAgainstTokenWithNonMatchingValueReturnsFalse(): void
    {
        $this->validator->setToken('foo');

        self::assertFalse($this->validator->isValid('bar'));
    }

    public function testValidatingAgainstTokenWithNonMatchingValueSetsNotSameMessage(): void
    {
        $this->testValidatingAgainstTokenWithNonMatchingValueReturnsFalse();

        $messages = $this->validator->getMessages();

        self::assertArrayHasKey('notSame', $messages);
    }

    public function testValidatingAgainstTokenWithMatchingValueReturnsTrue(): void
    {
        $this->validator->setToken('foo');

        self::assertTrue($this->validator->isValid('foo'));
    }

    /**
     * @group Laminas-6953
     */
    public function testValidatingAgainstEmptyToken(): void
    {
        $this->validator->setToken('');

        self::assertTrue($this->validator->isValid(''));
    }

    /**
     * @group Laminas-7128
     */
    public function testValidatingAgainstNonStrings(): void
    {
        $this->validator->setToken(true);

        self::assertTrue($this->validator->isValid(true));
        self::assertFalse($this->validator->isValid(1));

        $this->validator->setToken(['one' => 'two', 'three']);

        self::assertTrue($this->validator->isValid(['one' => 'two', 'three']));
        self::assertFalse($this->validator->isValid([]));
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

        $validator->setStrict(true);

        self::assertFalse($validator->isValid(['token' => '123']));
    }

    public function testEqualsMessageTemplates(): void
    {
        self::assertSame(
            [
                Identical::NOT_SAME,
                Identical::MISSING_TOKEN,
            ],
            array_keys($this->validator->getMessageTemplates())
        );
        self::assertSame($this->validator->getOption('messageTemplates'), $this->validator->getMessageTemplates());
    }

    public function testEqualsMessageVariables(): void
    {
        $messageVariables = ['token' => 'tokenString'];

        self::assertSame($messageVariables, $this->validator->getOption('messageVariables'));
        self::assertSame(array_keys($messageVariables), $this->validator->getMessageVariables());
    }

    public function testValidatingStringTokenInContext(): void
    {
        $this->validator->setToken('email');

        self::assertTrue($this->validator->isValid(
            'john@doe.com',
            ['email' => 'john@doe.com']
        ));

        self::assertFalse($this->validator->isValid(
            'john@doe.com',
            ['email' => 'harry@hoe.com']
        ));

        self::assertFalse($this->validator->isValid(
            'harry@hoe.com',
            ['email' => 'john@doe.com']
        ));

        self::assertTrue($this->validator->isValid(
            'john@doe.com',
            new Parameters(['email' => 'john@doe.com'])
        ));

        self::assertFalse($this->validator->isValid(
            'john@doe.com',
            new Parameters(['email' => 'harry@hoe.com'])
        ));

        self::assertFalse($this->validator->isValid(
            'harry@hoe.com',
            new Parameters(['email' => 'john@doe.com'])
        ));
    }

    public function testValidatingArrayTokenInContext(): void
    {
        $this->validator->setToken(['user' => 'email']);

        self::assertTrue($this->validator->isValid(
            'john@doe.com',
            [
                'user' => [
                    'email' => 'john@doe.com',
                ],
            ]
        ));

        self::assertFalse($this->validator->isValid(
            'john@doe.com',
            [
                'user' => [
                    'email' => 'harry@hoe.com',
                ],
            ]
        ));

        self::assertFalse($this->validator->isValid(
            'harry@hoe.com',
            [
                'user' => [
                    'email' => 'john@doe.com',
                ],
            ]
        ));

        self::assertTrue($this->validator->isValid(
            'john@doe.com',
            new Parameters([
                'user' => [
                    'email' => 'john@doe.com',
                ],
            ])
        ));

        self::assertFalse($this->validator->isValid(
            'john@doe.com',
            new Parameters([
                'user' => [
                    'email' => 'harry@hoe.com',
                ],
            ])
        ));

        self::assertFalse($this->validator->isValid(
            'harry@hoe.com',
            new Parameters([
                'user' => [
                    'email' => 'john@doe.com',
                ],
            ])
        ));
    }

    public function testCanSetLiteralParameterThroughConstructor(): void
    {
        $validator = new Identical(['token' => 'foo', 'literal' => true]);
        // Default is false
        $validator->setLiteral(true);

        self::assertTrue($validator->getLiteral());
    }

    public function testLiteralParameterDoesNotAffectValidationWhenNoContextIsProvided(): void
    {
        $this->validator->setToken(['foo' => 'bar']);
        $this->validator->setLiteral(false);

        self::assertTrue($this->validator->isValid(['foo' => 'bar']));

        $this->validator->setLiteral(true);

        self::assertTrue($this->validator->isValid(['foo' => 'bar']));
    }

    public function testLiteralParameterWorksWhenContextIsProvided(): void
    {
        $this->validator->setToken(['foo' => 'bar']);
        $this->validator->setLiteral(true);

        self::assertTrue($this->validator->isValid(
            ['foo' => 'bar'],
            ['foo' => 'baz'] // Provide a context to make sure the literal parameter will work
        ));
    }

    /**
     * @dataProvider invalidContextProvider
     * @param mixed $context
     */
    public function testIsValidThrowsExceptionOnInvalidContext($context): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->validator->isValid('john@doe.com', $context);
    }

    /**
     * @return mixed[][]
     */
    public function invalidContextProvider(): array
    {
        return [
            [false],
            [new stdClass()],
            ['dummy'],
        ];
    }
}
