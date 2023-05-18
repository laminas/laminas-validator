<?php

declare(strict_types=1);

namespace LaminasTest\Validator;

use ArrayIterator;
use DateTime;
use Exception;
use Laminas\Validator\Exception\InvalidArgumentException;
use Laminas\Validator\IsInstanceOf;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * @covers \Laminas\Validator\IsInstanceOf
 * @group Laminas_Validator
 */
final class IsInstanceOfTest extends TestCase
{
    /**
     * Ensures that the validator follows expected behavior
     */
    public function testBasic(): void
    {
        $validator = new IsInstanceOf(DateTime::class);

        self::assertTrue($validator->isValid(new DateTime())); // True
        self::assertFalse($validator->isValid(null)); // False
        self::assertFalse($validator->isValid($this)); // False

        $validator = new IsInstanceOf(Exception::class);

        self::assertTrue($validator->isValid(new Exception())); // True
        self::assertFalse($validator->isValid(null)); // False
        self::assertFalse($validator->isValid($this)); // False

        $validator = new IsInstanceOf(TestCase::class);

        self::assertTrue($validator->isValid($this)); // True
    }

    /**
     * Ensures that getMessages() returns expected default value
     */
    public function testGetMessages(): void
    {
        $validator = new IsInstanceOf(DateTime::class);

        self::assertSame([], $validator->getMessages());
    }

    /**
     * Ensures that getClassName() returns expected value
     */
    public function testGetClassName(): void
    {
        $validator = new IsInstanceOf(DateTime::class);

        self::assertSame(DateTime::class, $validator->getClassName());
    }

    public function testEqualsMessageTemplates(): void
    {
        $validator  = new IsInstanceOf(DateTime::class);
        $reflection = new ReflectionClass($validator);

        $property = $reflection->getProperty('messageTemplates');

        self::assertSame(
            $property->getValue($validator),
            $validator->getOption('messageTemplates')
        );
    }

    public function testEqualsMessageVariables(): void
    {
        $validator  = new IsInstanceOf(DateTime::class);
        $reflection = new ReflectionClass($validator);

        $property = $reflection->getProperty('messageVariables');

        self::assertSame(
            $property->getValue($validator),
            $validator->getOption('messageVariables')
        );
    }

    public function testPassTraversableToConstructor(): void
    {
        $validator = new IsInstanceOf(new ArrayIterator(['className' => DateTime::class]));

        self::assertSame(DateTime::class, $validator->getClassName());
        self::assertTrue($validator->isValid(new DateTime()));
        self::assertFalse($validator->isValid(null));
        self::assertFalse($validator->isValid($this));
    }

    public function testPassOptionsWithoutClassNameKey(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing option "className"');

        new IsInstanceOf(['NotClassNameKey' => DateTime::class]);
    }
}
