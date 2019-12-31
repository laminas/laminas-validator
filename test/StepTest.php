<?php

/**
 * @see       https://github.com/laminas/laminas-validator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-validator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-validator/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Validator;

use Laminas\Validator;

/**
 * @category   Laminas
 * @package    Laminas_Validator
 * @subpackage UnitTests
 * @group      Laminas_Validator
 */
class StepTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Step object
     *
     * @var \Laminas\Validator\Step
     */
    protected $_validator;

    /**
     * Creates a new Laminas\Validator\Step object for each test method
     *
     * @return void
     */
    public function setUp()
    {
        $this->_validator = new Validator\Step();
    }

    /**
     * Ensures that the validator follows expected behavior
     *
     * @return void
     */
    public function testBasic()
    {
        // By default, baseValue == 0 and step == 1
        $valuesExpected = array(
            array(1.00, true),
            array(0.00, true),
            array(2, true),
            array(3, true),
            array(2.1, false),
            array('2', true),
            array('1', true),
            array('1.2', false),
            array(1.01, false),
            array('not a scalar', false)
        );

        foreach ($valuesExpected as $element) {
            $this->assertEquals($element[1], $this->_validator->isValid($element[0]),
                'Test failed with ' . var_export($element, 1));
        }
    }

    public function testDecimalBaseValue()
    {
        $valuesExpected = array(
            array(1.1, false),
            array(0.1, true),
            array(2.1, true),
            array(3.1, false),
            array('2.1', true),
            array('1.1', false),
            array(1.11, false),
            array('not a scalar', false)
        );

        $validator = new Validator\Step(array(
            'baseValue' => 0.1,
            'step'      => 2
        ));

        foreach ($valuesExpected as $element) {
            $this->assertEquals($element[1], $validator->isValid($element[0]),
                'Test failed with ' . var_export($element, 1));
        }
    }

    public function testDecimalStep()
    {
        $valuesExpected = array(
            array(1.1, false),
            array(0.1, false),
            array(2.1, true),
            array(3.1, false),
            array(10.5, true),
            array('2.1', true),
            array('1.1', false),
            array(1.11, false),
            array('not a scalar', false)
        );

        $validator = new Validator\Step(array(
            'baseValue' => 0,
            'step'      => 2.1
        ));

        foreach ($valuesExpected as $element) {
            $this->assertEquals($element[1], $validator->isValid($element[0]),
                'Test failed with ' . var_export($element, 1));
        }
    }

    /**
     * Ensures that getMessages() returns expected default value
     *
     * @return void
     */
    public function testGetMessages()
    {
        $this->assertEquals(array(), $this->_validator->getMessages());
    }

    /**
     * Ensures that set/getBaseValue() works
     */
    public function testCanSetBaseValue()
    {
        $this->_validator->setBaseValue(2);
        $this->assertEquals('2', $this->_validator->getBaseValue());
    }

    /**
     * Ensures that set/getStep() works
     */
    public function testCanSetStepValue()
    {
        $this->_validator->setStep(2);
        $this->assertEquals('2', $this->_validator->getStep());
    }

    public function testEqualsMessageTemplates()
    {
        $validator = new Validator\Step();
        $this->assertAttributeEquals($validator->getOption('messageTemplates'),
                                     'messageTemplates', $validator);
    }
}
