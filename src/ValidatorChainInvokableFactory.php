<?php

declare(strict_types=1);

namespace Laminas\Validator;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

/**
 * This factory enables chain creation via the plugin manager.
 *
 * Example:
 *  $chain = $pluginManager->get(ValidatorChain::class); // Empty chain
 *  $chain = $pluginManager->build(ValidatorChain::class, $options); // Configured Chain
 *
 * @psalm-import-type ValidatorSpecification from ValidatorInterface
 * @psalm-internal Laminas\Validator
 * @psalm-internal LaminasTest\Validator
 */
final class ValidatorChainInvokableFactory implements FactoryInterface
{
    /**
     * @param array<array-key, ValidatorSpecification|ValidatorInterface>|null $options
     * @psalm-suppress MoreSpecificImplementedParamType $options is more specific to prevent psalm from requiring
     *                 runtime validation of the validator chain options payload.
     */
    public function __invoke(
        ContainerInterface $container,
        string $requestedName,
        ?array $options = null,
    ): ValidatorChain {
        $options ??= [];
        $factory   = $container->get(ValidatorChainFactory::class);
        return $factory->fromArray($options);
    }
}
