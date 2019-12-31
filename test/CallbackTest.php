<?php

/**
 * @see       https://github.com/laminas/laminas-validator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-validator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-validator/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Validator;

use Laminas\Validator\Callback;

/**
 * @category   Laminas
 * @package    Laminas_Validator
 * @subpackage UnitTests
 * @group      Laminas_Validator
 */
class CallbackTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Ensures that the validator follows expected behavior
     *
     * @return void
     */
    public function testBasic()
    {
        $valid = new Callback(array($this, 'objectCallback'));
        $this->assertTrue($valid->isValid('test'));
    }

    public function testStaticCallback()
    {
        $valid = new Callback(
            array('\LaminasTest\Validator\CallbackTest', 'staticCallback')
        );
        $this->assertTrue($valid->isValid('test'));
    }

    public function testSettingDefaultOptionsAfterwards()
    {
        $valid = new Callback(array($this, 'objectCallback'));
        $valid->setCallbackOptions('options');
        $this->assertEquals(array('options'), $valid->getCallbackOptions());
        $this->assertTrue($valid->isValid('test'));
    }

    public function testSettingDefaultOptions()
    {
        $valid = new Callback(array('callback' => array($this, 'objectCallback'), 'callbackOptions' => 'options'));
        $this->assertEquals(array('options'), $valid->getCallbackOptions());
        $this->assertTrue($valid->isValid('test'));
    }

    public function testGettingCallback()
    {
        $valid = new Callback(array($this, 'objectCallback'));
        $this->assertEquals(array($this, 'objectCallback'), $valid->getCallback());
    }

    public function testInvalidCallback()
    {
        $valid = new Callback(array($this, 'objectCallback'));

        $this->setExpectedException('Laminas\Validator\Exception\InvalidArgumentException', 'Invalid callback given');
        $valid->setCallback('invalidcallback');
    }

    public function testAddingValueOptions()
    {
        $valid = new Callback(array('callback' => array($this, 'optionsCallback'), 'callbackOptions' => 'options'));
        $this->assertEquals(array('options'), $valid->getCallbackOptions());
        $this->assertTrue($valid->isValid('test', 'something'));
    }

    public function testEqualsMessageTemplates()
    {
        $validator = new Callback(array($this, 'objectCallback'));
        $this->assertAttributeEquals($validator->getOption('messageTemplates'),
                                     'messageTemplates', $validator);
    }

    public function testCanAcceptContextWithoutOptions()
    {
        $value     = 'bar';
        $context   = array('foo' => 'bar', 'bar' => 'baz');
        $validator = new Callback(function($v, $c) use ($value, $context) {
            return (($value == $v) && ($context == $c));
        });
        $this->assertTrue($validator->isValid($value, $context));
    }

    public function testCanAcceptContextWithOptions()
    {
        $value     = 'bar';
        $context   = array('foo' => 'bar', 'bar' => 'baz');
        $options   = array('baz' => 'bat');
        $validator = new Callback(function($v, $c, $baz) use ($value, $context, $options) {
            return (($value == $v) && ($context == $c) && ($options['baz'] == $baz));
        });
        $validator->setCallbackOptions($options);
        $this->assertTrue($validator->isValid($value, $context));
    }

    public function objectCallback($value)
    {
        return true;
    }

    public static function staticCallback($value)
    {
        return true;
    }

    public function optionsCallback($value)
    {
        $args = func_get_args();
        $this->assertContains('something', $args);
        return $args;
    }
}
