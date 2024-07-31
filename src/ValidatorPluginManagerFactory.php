<?php

declare(strict_types=1);

namespace Laminas\Validator;

use Laminas\ServiceManager\ServiceManager;
use Psr\Container\ContainerInterface;

use function is_array;

/**
 * @link ServiceManager
 *
 * @psalm-import-type ServiceManagerConfiguration from ServiceManager
 */
final class ValidatorPluginManagerFactory
{
    public function __invoke(ContainerInterface $container): ValidatorPluginManager
    {
        // If this is in a laminas-mvc application, the ServiceListener will inject
        // merged configuration during bootstrap.
        if ($container->has('ServiceListener')) {
            return new ValidatorPluginManager($container);
        }

        // If we do not have a config service, nothing more to do
        if (! $container->has('config')) {
            return new ValidatorPluginManager($container);
        }

        $config = $container->get('config');

        // If we do not have validators configuration, nothing more to do
        if (! isset($config['validators']) || ! is_array($config['validators'])) {
            return new ValidatorPluginManager($container);
        }

        return new ValidatorPluginManager($container, $config['validators']);
    }
}
