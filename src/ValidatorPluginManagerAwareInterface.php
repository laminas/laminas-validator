<?php

declare(strict_types=1);

namespace Laminas\Validator;

interface ValidatorPluginManagerAwareInterface
{
    /**
     * Set validator plugin manager
     *
     * @return void
     */
    public function setValidatorPluginManager(ValidatorPluginManager $pluginManager);

    /**
     * Get validator plugin manager
     *
     * @return ValidatorPluginManager
     */
    public function getValidatorPluginManager();
}
