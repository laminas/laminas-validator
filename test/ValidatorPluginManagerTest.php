<?php

/**
 * @see       https://github.com/laminas/laminas-validator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-validator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-validator/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Validator;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Exception\InvalidServiceException;
use Laminas\ServiceManager\ServiceManager;
use Laminas\Validator\Exception\RuntimeException;
use Laminas\Validator\ValidatorInterface;
use Laminas\Validator\ValidatorPluginManager;
use PHPUnit\Framework\TestCase;

/**
 * @group      Laminas_Validator
 */
class ValidatorPluginManagerTest extends TestCase
{
    protected function setUp() : void
    {
        $this->validators = new ValidatorPluginManager(new ServiceManager);
    }

    public function testAllowsInjectingTranslator()
    {
        $translator = $this->prophesize(TestAsset\Translator::class)->reveal();

        $container = $this->prophesize(ContainerInterface::class);
        $container->has('MvcTranslator')->willReturn(true);
        $container->get('MvcTranslator')->willReturn($translator);

        $validators = new ValidatorPluginManager($container->reveal());

        $validator = $validators->get('notempty');
        $this->assertEquals($translator, $validator->getTranslator());
    }

    public function testNoTranslatorInjectedWhenTranslatorIsNotPresent()
    {
        $container = $this->prophesize(ContainerInterface::class);
        $container->has('MvcTranslator')->willReturn(false);

        $validators = new ValidatorPluginManager($container->reveal());

        $validator = $validators->get('notempty');
        $this->assertNull($validator->getTranslator());
    }

    public function testRegisteringInvalidValidatorRaisesException()
    {
        try {
            $this->validators->setService('test', $this);
        } catch (InvalidServiceException $e) {
            $this->assertStringContainsString(ValidatorInterface::class, $e->getMessage());
        } catch (RuntimeException $e) {
            $this->assertStringContainsString(ValidatorInterface::class, $e->getMessage());
        } catch (\Exception $e) {
            $this->fail(sprintf(
                'Unexpected exception of type "%s" when testing for invalid validator types',
                get_class($e)
            ));
        }
    }

    public function testLoadingInvalidValidatorRaisesException()
    {
        $this->validators->setInvokableClass('test', get_class($this));
        try {
            $this->validators->get('test');
        } catch (InvalidServiceException $e) {
            $this->assertStringContainsString(ValidatorInterface::class, $e->getMessage());
        } catch (RuntimeException $e) {
            $this->assertStringContainsString(ValidatorInterface::class, $e->getMessage());
        } catch (\Exception $e) {
            $this->fail(sprintf(
                'Unexpected exception of type "%s" when testing for invalid validator types',
                get_class($e)
            ));
        }
    }

    public function testInjectedValidatorPluginManager()
    {
        $validator = $this->validators->get('explode');
        $this->assertSame($this->validators, $validator->getValidatorPluginManager());
    }
}
