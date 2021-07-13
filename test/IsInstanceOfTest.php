<?php

namespace LaminasTest\Validator;

use ArrayIterator;
use DateTime;
use Exception;
use Laminas\Validator;
use Laminas\Validator\Exception\InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * @covers \Laminas\Validator\IsInstanceOf
 * @group      Laminas_Validator
 */
class IsInstanceOfTest extends TestCase
{
    /**
     * Ensures that the validator follows expected behavior
     *
     * @return void
     */
    public function testBasic()
    {
        $validator = new Validator\IsInstanceOf(DateTime::class);
        $this->assertTrue($validator->isValid(new DateTime())); // True
        $this->assertFalse($validator->isValid(null)); // False
        $this->assertFalse($validator->isValid($this)); // False

        $validator = new Validator\IsInstanceOf(Exception::class);
        $this->assertTrue($validator->isValid(new Exception())); // True
        $this->assertFalse($validator->isValid(null)); // False
        $this->assertFalse($validator->isValid($this)); // False

        $validator = new Validator\IsInstanceOf(TestCase::class);
        $this->assertTrue($validator->isValid($this)); // True
    }

    /**
     * Ensures that getMessages() returns expected default value
     *
     * @return void
     */
    public function testGetMessages()
    {
        $validator = new Validator\IsInstanceOf(DateTime::class);
        $this->assertEquals([], $validator->getMessages());
    }

    /**
     * Ensures that getClassName() returns expected value
     *
     * @return void
     */
    public function testGetClassName()
    {
        $validator = new Validator\IsInstanceOf(DateTime::class);
        $this->assertEquals(DateTime::class, $validator->getClassName());
    }

    public function testEqualsMessageTemplates(): void
    {
        $validator  = new Validator\IsInstanceOf(DateTime::class);
        $reflection = new ReflectionClass($validator);

        $property = $reflection->getProperty('messageTemplates');
        $property->setAccessible(true);

        $this->assertEquals(
            $property->getValue($validator),
            $validator->getOption('messageTemplates')
        );
    }

    public function testEqualsMessageVariables(): void
    {
        $validator  = new Validator\IsInstanceOf(DateTime::class);
        $reflection = new ReflectionClass($validator);

        $property = $reflection->getProperty('messageVariables');
        $property->setAccessible(true);

        $this->assertEquals(
            $property->getValue($validator),
            $validator->getOption('messageVariables')
        );
    }

    public function testPassTraversableToConstructor(): void
    {
        $validator = new Validator\IsInstanceOf(new ArrayIterator(['className' => DateTime::class]));
        $this->assertEquals(DateTime::class, $validator->getClassName());
        $this->assertTrue($validator->isValid(new DateTime()));
        $this->assertFalse($validator->isValid(null));
        $this->assertFalse($validator->isValid($this));
    }

    public function testPassOptionsWithoutClassNameKey(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing option "className"');

        $options   = ['NotClassNameKey' => DateTime::class];
        $validator = new Validator\IsInstanceOf($options);
    }
}
