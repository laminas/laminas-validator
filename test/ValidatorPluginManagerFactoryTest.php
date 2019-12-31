<?php

/**
 * @see       https://github.com/laminas/laminas-validator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-validator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-validator/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Validator;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\Validator\ValidatorInterface;
use Laminas\Validator\ValidatorPluginManager;
use Laminas\Validator\ValidatorPluginManagerFactory;
use PHPUnit\Framework\TestCase;

class ValidatorPluginManagerFactoryTest extends TestCase
{
    public function testFactoryReturnsPluginManager()
    {
        $container = $this->prophesize(ContainerInterface::class)->reveal();
        $factory = new ValidatorPluginManagerFactory();

        $validators = $factory($container, ValidatorPluginManagerFactory::class);
        $this->assertInstanceOf(ValidatorPluginManager::class, $validators);

        if (method_exists($validators, 'configure')) {
            // laminas-servicemanager v3
            $this->assertAttributeSame($container, 'creationContext', $validators);
        } else {
            // laminas-servicemanager v2
            $this->assertSame($container, $validators->getServiceLocator());
        }
    }

    /**
     * @depends testFactoryReturnsPluginManager
     */
    public function testFactoryConfiguresPluginManagerUnderContainerInterop()
    {
        $container = $this->prophesize(ContainerInterface::class)->reveal();
        $validator = $this->prophesize(ValidatorInterface::class)->reveal();

        $factory = new ValidatorPluginManagerFactory();
        $validators = $factory($container, ValidatorPluginManagerFactory::class, [
            'services' => [
                'test' => $validator,
            ],
        ]);
        $this->assertSame($validator, $validators->get('test'));
    }

    /**
     * @depends testFactoryReturnsPluginManager
     */
    public function testFactoryConfiguresPluginManagerUnderServiceManagerV2()
    {
        $container = $this->prophesize(ServiceLocatorInterface::class);
        $container->willImplement(ContainerInterface::class);

        $validator = $this->prophesize(ValidatorInterface::class)->reveal();

        $factory = new ValidatorPluginManagerFactory();
        $factory->setCreationOptions([
            'services' => [
                'test' => $validator,
            ],
        ]);

        $validators = $factory->createService($container->reveal());
        $this->assertSame($validator, $validators->get('test'));
    }
}
