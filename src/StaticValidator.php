<?php

/**
 * @see       https://github.com/laminas/laminas-validator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-validator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-validator/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Validator;

class StaticValidator
{
    /**
     * @var ValidatorPluginManager
     */
    protected static $plugins;

    /**
     * Set plugin manager to use for locating validators
     *
     * @param  ValidatorPluginManager|null $plugins
     * @return void
     */
    public static function setPluginManager(ValidatorPluginManager $plugins = null)
    {
        // Don't share by default to allow different arguments on subsequent calls
        if ($plugins instanceof ValidatorPluginManager) {
            $plugins->setShareByDefault(false);
        }
        static::$plugins = $plugins;
    }

    /**
     * Get plugin manager for locating validators
     *
     * @return ValidatorPluginManager
     */
    public static function getPluginManager()
    {
        if (null === static::$plugins) {
            static::setPluginManager(new ValidatorPluginManager());
        }
        return static::$plugins;
    }

    /**
     * @param  mixed    $value
     * @param  string   $classBaseName
     * @param  array    $args          OPTIONAL
     * @return bool
     */
    public static function execute($value, $classBaseName, array $args = [])
    {
        $plugins = static::getPluginManager();

        $validator = $plugins->get($classBaseName, $args);
        return $validator->isValid($value);
    }
}
