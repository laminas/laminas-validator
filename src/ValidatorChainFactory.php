<?php

declare(strict_types=1);

namespace Laminas\Validator;

/**
 * @psalm-import-type ValidatorSpecification from ValidatorInterface
 */
final class ValidatorChainFactory
{
    public function __construct(private readonly ValidatorPluginManager $pluginManager)
    {
    }

    /** @param array<array-key, ValidatorSpecification|ValidatorInterface> $specification */
    public function fromArray(array $specification): ValidatorChain
    {
        $chain = new ValidatorChain($this->pluginManager);
        foreach ($specification as $spec) {
            if ($spec instanceof ValidatorInterface) {
                $chain->attach($spec);
                continue;
            }

            $priority   = $spec['priority'] ?? ValidatorChainInterface::DEFAULT_PRIORITY;
            $breakChain = $spec['break_chain_on_failure'] ?? false;
            $options    = $spec['options'] ?? [];
            $chain->attachByName($spec['name'], $options, $breakChain, $priority);
        }

        return $chain;
    }
}
