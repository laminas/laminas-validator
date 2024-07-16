<?php

declare(strict_types=1);

namespace LaminasTest\Validator;

use DateTime;
use Exception;
use Laminas\Validator\Exception\InvalidArgumentException;
use Laminas\Validator\IsInstanceOf;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class IsInstanceOfTest extends TestCase
{
    /**
     * Ensures that the validator follows expected behavior
     */
    public function testBasic(): void
    {
        $validator = new IsInstanceOf(['className' => DateTime::class]);

        self::assertTrue($validator->isValid(new DateTime())); // True
        self::assertFalse($validator->isValid(null)); // False
        self::assertFalse($validator->isValid($this)); // False

        $validator = new IsInstanceOf(['className' => Exception::class]);

        self::assertTrue($validator->isValid(new Exception())); // True
        self::assertFalse($validator->isValid(null)); // False
        self::assertFalse($validator->isValid($this)); // False

        $validator = new IsInstanceOf(['className' => TestCase::class]);

        self::assertTrue($validator->isValid($this)); // True
    }

    /**
     * Ensures that getMessages() returns expected default value
     */
    public function testGetMessages(): void
    {
        $validator = new IsInstanceOf(['className' => DateTime::class]);

        self::assertSame([], $validator->getMessages());
    }

    public function testEqualsMessageTemplates(): void
    {
        $validator  = new IsInstanceOf(['className' => DateTime::class]);
        $reflection = new ReflectionClass($validator);

        $property = $reflection->getProperty('messageTemplates');

        self::assertSame(
            $property->getValue($validator),
            $validator->getOption('messageTemplates')
        );
    }

    public function testEqualsMessageVariables(): void
    {
        $validator  = new IsInstanceOf(['className' => DateTime::class]);
        $reflection = new ReflectionClass($validator);

        $property = $reflection->getProperty('messageVariables');

        self::assertSame(
            $property->getValue($validator),
            $validator->getOption('messageVariables')
        );
    }

    public function testPassOptionsWithoutClassNameKey(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The className option must be a non-empty class-string');

        /** @psalm-suppress InvalidArgument */
        new IsInstanceOf(['NotClassNameKey' => DateTime::class]);
    }
}
