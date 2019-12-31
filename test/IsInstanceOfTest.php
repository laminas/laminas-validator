<?php

/**
 * @see       https://github.com/laminas/laminas-validator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-validator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-validator/blob/master/LICENSE.md New BSD License
 */
namespace LaminasTest\Validator;

use DateTime;
use Laminas\Validator;
use ReflectionClass;

/**
 * @covers     Laminas\Validator\IsInstanceOf
 * @category   Laminas
 * @package    Laminas_Validator
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2012 Laminas (https://www.zend.com)
 * @license    https://getlaminas.org/license/new-bsd     New BSD License
 * @group      Laminas_Validator
 */
class IsInstanceOfTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Ensures that the validator follows expected behavior
     *
     * @return void
     */
    public function testBasic()
    {
        $validator = new Validator\IsInstanceOf('DateTime');
        $this->assertTrue($validator->isValid(new DateTime())); // True
        $this->assertFalse($validator->isValid(null)); // False
        $this->assertFalse($validator->isValid($this)); // False

        $validator = new Validator\IsInstanceOf('Exception');
        $this->assertTrue($validator->isValid(new \Exception())); // True
        $this->assertFalse($validator->isValid(null)); // False
        $this->assertFalse($validator->isValid($this)); // False

        $validator = new Validator\IsInstanceOf('PHPUnit_Framework_TestCase');
        $this->assertTrue($validator->isValid($this)); // True
    }

    /**
     * Ensures that getMessages() returns expected default value
     *
     * @return void
     */
    public function testGetMessages()
    {
        $validator = new Validator\IsInstanceOf('DateTime');
        $this->assertEquals(array(), $validator->getMessages());
    }

    /**
     * Ensures that getClassName() returns expected value
     *
     * @return void
     */
    public function testGetClassName()
    {
        $validator = new Validator\IsInstanceOf('DateTime');
        $this->assertEquals('DateTime', $validator->getClassName());
    }

    public function testEqualsMessageTemplates()
    {
        $validator  = new Validator\IsInstanceOf('DateTime');
        $reflection = new ReflectionClass($validator);

        $property = $reflection->getProperty('messageTemplates');
        $property->setAccessible(true);

        $this->assertEquals(
            $property->getValue($validator),
            $validator->getOption('messageTemplates')
        );
    }

    public function testEqualsMessageVariables()
    {
        $validator  = new Validator\IsInstanceOf('\DateTime');
        $reflection = new ReflectionClass($validator);

        $property = $reflection->getProperty('messageVariables');
        $property->setAccessible(true);

        $this->assertEquals(
            $property->getValue($validator),
            $validator->getOption('messageVariables')
        );
    }
}
