<?php

declare(strict_types=1);

namespace Laminas\Validator;

interface ValidatorChainInterface extends ValidatorInterface
{
    public const DEFAULT_PRIORITY = 1;

    /**
     * Attach a validator to the end of the chain
     *
     * @param ValidatorInterface $validator A Validator implementation
     * @param bool $breakChainOnFailure If true, the chain's next validator will not be executed in case of failure
     * @param int $priority Priority at which to enqueue validator; defaults to 1 (higher executes earlier)
     */
    public function attach(
        ValidatorInterface $validator,
        bool $breakChainOnFailure = false,
        int $priority = self::DEFAULT_PRIORITY
    ): void;

    /**
     * Attach a validator to the chain using an alias or FQCN
     *
     * Retrieves the validator from the composed plugin manager, and then calls attach() with the retrieved instance.
     *
     * @param string|class-string<ValidatorInterface> $name
     * @param array<string, mixed> $options Construction options for the desired validator
     * @param bool $breakChainOnFailure If true, the chain's next validator will not be executed in case of failure
     * @param int $priority Priority at which to enqueue validator; defaults to 1 (higher executes earlier)
     */
    public function attachByName(
        string $name,
        array $options = [],
        bool $breakChainOnFailure = false,
        int $priority = self::DEFAULT_PRIORITY
    ): void;
}
