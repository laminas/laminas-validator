<?php

declare(strict_types=1);

namespace LaminasTest\Validator\StaticAnalysis;

use Laminas\ServiceManager\ServiceManager;
use Laminas\Validator\Uuid;
use Laminas\Validator\ValidatorInterface;
use Laminas\Validator\ValidatorPluginManager;

/** @psalm-suppress UnusedClass */
final class PluginManager
{
    public function validateAssertsPluginType(mixed $input): ValidatorInterface
    {
        (new ValidatorPluginManager(new ServiceManager()))->validate($input);

        return $input;
    }

    public function getWithClassStringReturnsCorrectInstanceType(): ValidatorInterface
    {
        return (new ValidatorPluginManager(new ServiceManager()))->get(Uuid::class);
    }
}
