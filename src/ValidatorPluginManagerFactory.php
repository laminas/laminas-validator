<?php

/**
 * @see       https://github.com/laminas/laminas-validator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-validator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-validator/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Validator;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

class ValidatorPluginManagerFactory implements FactoryInterface
{
    /**
     * laminas-servicemanager v2 support for invocation options.
     *
     * @param array
     */
    protected $creationOptions;

    /**
     * {@inheritDoc}
     *
     * @return ValidatorPluginManager
     */
    public function __invoke(ContainerInterface $container, $name, array $options = null)
    {
        return new ValidatorPluginManager($container, $options ?: []);
    }

    /**
     * {@inheritDoc}
     *
     * @return ValidatorPluginManager
     */
    public function createService(ServiceLocatorInterface $container, $name = null, $requestedName = null)
    {
        return $this($container, $requestedName ?: ValidatorPluginManager::class, $this->creationOptions);
    }

    /**
     * laminas-servicemanager v2 support for invocation options.
     *
     * @param array $options
     * @return void
     */
    public function setCreationOptions(array $options)
    {
        $this->creationOptions = $options;
    }
}
