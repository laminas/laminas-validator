<?php

declare(strict_types=1);

namespace Laminas\Validator;

use Laminas\Validator\Exception\InvalidArgumentException;

use function is_array;
use function is_bool;
use function is_int;
use function is_string;

/**
 * @psalm-import-type ValidatorSpecification from ValidatorInterface
 */
final class ValidatorChainFactory
{
    public function __construct(private readonly ValidatorPluginManager $pluginManager)
    {
    }

    /** @param array<array-key, mixed> $specification */
    public function fromArray(array $specification): ValidatorChain
    {
        $chain = new ValidatorChain($this->pluginManager);
        /** @psalm-var mixed $spec */
        foreach ($specification as $spec) {
            if ($spec instanceof ValidatorInterface) {
                $chain->attach($spec);
                continue;
            }

            $spec = $this->assertSpec($spec);

            $priority   = $spec['priority'] ?? ValidatorChainInterface::DEFAULT_PRIORITY;
            $breakChain = $spec['break_chain_on_failure'] ?? false;
            $options    = $spec['options'] ?? [];
            $chain->attachByName($spec['name'], $options, $breakChain, $priority);
        }

        return $chain;
    }

    /** @return ValidatorSpecification */
    private function assertSpec(mixed $spec): array
    {
        if (! is_array($spec)) {
            throw new InvalidArgumentException('Validator specifications must be an array');
        }

        if (! isset($spec['name']) || ! is_string($spec['name']) || $spec['name'] === '') {
            throw new InvalidArgumentException('Validator specifications should have a `name` key');
        }

        if (isset($spec['priority']) && ! is_int($spec['priority'])) {
            throw new InvalidArgumentException('Validator priorities must be integers');
        }

        if (isset($spec['options']) && ! is_array($spec['options'])) {
            throw new InvalidArgumentException('Validator options should be arrays');
        }

        if (isset($spec['break_chain_on_failure']) && ! is_bool($spec['break_chain_on_failure'])) {
            throw new InvalidArgumentException('The `break_chain_on_failure` key must contain a boolean when set');
        }

        /** @psalm-var ValidatorSpecification */
        return $spec;
    }
}
