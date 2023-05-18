<?php

declare(strict_types=1);

namespace LaminasTest\Validator\StaticAnalysis;

use Laminas\Validator\Uuid;
use Laminas\Validator\ValidatorInterface;
use Laminas\Validator\ValidatorPluginManager;

/** @psalm-suppress UnusedClass */
final class PluginManager
{
    public function validateAssertsPluginType(mixed $input): ValidatorInterface
    {
        (new ValidatorPluginManager())->validate($input);

        return $input;
    }

    public function getWithClassStringReturnsCorrectInstanceType(): ValidatorInterface
    {
        return (new ValidatorPluginManager())->get(Uuid::class);
    }
}
