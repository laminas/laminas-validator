<?php

/**
 * @see       https://github.com/laminas/laminas-validator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-validator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-validator/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Validator;

use Laminas\Validator\Explode;

/**
 * @category   Laminas
 * @package    Laminas_Validator
 * @subpackage UnitTests
 * @group      Laminas_Validator
 */
class ExplodeTest extends \PHPUnit_Framework_TestCase
{
    public function testRaisesExceptionWhenValidatorIsMissing()
    {
        $validator = new Explode();
        $this->setExpectedException('Laminas\Validator\Exception\RuntimeException', 'validator');
        $validator->isValid('foo,bar');
    }

    public function getExpectedData()
    {
        return array(
            //    value              delim break  N  valid  messages                   expects
            array('foo,bar,dev,null', ',', false, 4, true,  array(),                   true),
            array('foo,bar,dev,null', ',', true,  1, false, array('X'),                false),
            array('foo,bar,dev,null', ',', false, 4, false, array('X'),                false),
            array('foo,bar,dev,null', ';', false, 1, true,  array(),                   true),
            array('foo;bar,dev;null', ',', false, 2, true,  array(),                   true),
            array('foo;bar,dev;null', ',', false, 2, false, array('X'),                false),
            array('foo;bar;dev;null', ';', false, 4, true,  array(),                   true),
            array('foo',              ',', false, 1, true,  array(),                   true),
            array('foo',              ',', false, 1, false, array('X'),                false),
            array('foo',              ',', true,  1, false, array('X'),                false),
            array(array('a', 'b'),   null, false, 2, true,  array(),                   true),
            array(array('a', 'b'),   null, false, 2, false, array('X'),                false),
            array('foo',             null, false, 1, true,  array(),                   true),
            array(1,                  ',', false, 1, true,  array(),                   true),
            array(null,               ',', false, 1, true,  array(),                   true),
            array(new \stdClass(),    ',', false, 1, true,  array(),                   true),
            array(new \ArrayObject(array('a', 'b')), null, false, 2, true,  array(),   true),
        );
    }

    /**
     * @dataProvider getExpectedData
     */
    public function testExpectedBehavior($value, $delimiter, $breakOnFirst, $numIsValidCalls, $isValidReturn, $messages, $expects)
    {
        $mockValidator = $this->getMock('Laminas\Validator\ValidatorInterface');
        $mockValidator->expects($this->exactly($numIsValidCalls))->method('isValid')->will($this->returnValue($isValidReturn));
        $mockValidator->expects($this->any())->method('getMessages')->will($this->returnValue('X'));

        $validator = new Explode(array(
            'validator'           => $mockValidator,
            'valueDelimiter'      => $delimiter,
            'breakOnFirstFailure' => $breakOnFirst,
        ));

        $this->assertEquals($expects,  $validator->isValid($value));
        $this->assertEquals($messages, $validator->getMessages());
    }

    public function testGetMessagesReturnsDefaultValue()
    {
        $validator = new Explode();
        $this->assertEquals(array(), $validator->getMessages());
    }

    public function testEqualsMessageTemplates()
    {
        $validator = new Explode(array());
        $this->assertAttributeEquals($validator->getOption('messageTemplates'),
                                     'messageTemplates', $validator);
    }

    public function testEqualsMessageVariables()
    {
        $validator = new Explode(array());
        $this->assertAttributeEquals($validator->getOption('messageVariables'),
                                     'messageVariables', $validator);
    }

    public function testSetValidatorAsArray()
    {
        $validator = new Explode();
        $validator->setValidator(
            array(
                'name' => 'inarray',
                'options' => array(
                    'haystack' => array(
                        'a', 'b', 'c'
                    )
                )
            )
        );

        /** @var $inArrayValidator \Laminas\Validator\InArray */
        $inArrayValidator = $validator->getValidator();
        $this->assertInstanceOf('Laminas\Validator\InArray', $inArrayValidator);
        $this->assertSame(
            array('a', 'b', 'c'), $inArrayValidator->getHaystack()
        );
    }

    /**
     * @expectedException \Laminas\Validator\Exception\RuntimeException
     */
    public function testSetValidatorMissingName()
    {
        $validator = new Explode();
        $validator->setValidator(
            array(
                'options' => array()
            )
        );
    }

    /**
     * @expectedException \Laminas\Validator\Exception\RuntimeException
     */
    public function testSetValidatorInvalidParam()
    {
        $validator = new Explode();
        $validator->setValidator('inarray');
    }
}
