<?php

/**
 * @see       https://github.com/laminas/laminas-validator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-validator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-validator/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Validator;

use Laminas\Validator\ValidatorPluginManager;

/**
 * @group      Laminas_Validator
 */
class ValidatorPluginManagerTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->validators = new ValidatorPluginManager();
    }

    public function testAllowsInjectingTranslator()
    {
        $translator = $this->getMock('LaminasTest\Validator\TestAsset\Translator');

        $slContents = [['MvcTranslator', $translator]];
        $serviceLocator = $this->getMock('Laminas\ServiceManager\ServiceLocatorInterface');
        $serviceLocator->expects($this->once())
            ->method('get')
            ->will($this->returnValueMap($slContents));
        $serviceLocator->expects($this->once())
            ->method('has')
            ->with($this->equalTo('MvcTranslator'))
            ->will($this->returnValue(true));

        $this->validators->setServiceLocator($serviceLocator);
        $this->assertSame($serviceLocator, $this->validators->getServiceLocator());

        $validator = $this->validators->get('notempty');
        $this->assertSame($translator, $validator->getTranslator());
    }

    public function testNoTranslatorInjectedWhenTranslatorIsNotPresent()
    {
        $serviceLocator = $this->getMock('Laminas\ServiceManager\ServiceLocatorInterface');
        $serviceLocator->expects($this->once())
            ->method('has')
            ->with($this->equalTo('MvcTranslator'))
            ->will($this->returnValue(false));

        $this->validators->setServiceLocator($serviceLocator);
        $this->assertSame($serviceLocator, $this->validators->getServiceLocator());

        $validator = $this->validators->get('notempty');
        $this->assertNull($validator->getTranslator());
    }

    public function testRegisteringInvalidValidatorRaisesException()
    {
        $this->setExpectedException('Laminas\Validator\Exception\RuntimeException');
        $this->validators->setService('test', $this);
    }

    public function testLoadingInvalidValidatorRaisesException()
    {
        $this->validators->setInvokableClass('test', get_class($this));
        $this->setExpectedException('Laminas\Validator\Exception\RuntimeException');
        $this->validators->get('test');
    }

    public function testInjectedValidatorPluginManager()
    {
        $validator = $this->validators->get('explode');
        $this->assertSame($this->validators, $validator->getValidatorPluginManager());
    }
}
