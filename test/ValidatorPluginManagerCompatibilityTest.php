<?php

/**
 * @see       https://github.com/laminas/laminas-validator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-validator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-validator/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Validator;

use Laminas\ServiceManager\ServiceManager;
use Laminas\ServiceManager\Test\CommonPluginManagerTrait;
use Laminas\Validator\Exception\RuntimeException;
use Laminas\Validator\ValidatorInterface;
use Laminas\Validator\ValidatorPluginManager;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

class ValidatorPluginManagerCompatibilityTest extends TestCase
{
    use CommonPluginManagerTrait;

    protected function getPluginManager()
    {
        return new ValidatorPluginManager(new ServiceManager());
    }

    protected function getV2InvalidPluginException()
    {
        return RuntimeException::class;
    }

    protected function getInstanceOf()
    {
        return ValidatorInterface::class;
    }

    public function aliasProvider()
    {
        $pluginManager = $this->getPluginManager();
        $r = new ReflectionProperty($pluginManager, 'aliases');
        $r->setAccessible(true);
        $aliases = $r->getValue($pluginManager);

        foreach ($aliases as $alias => $target) {
            // Skipping due to required options
            if (strpos($target, '\\Barcode')) {
                continue;
            }

            // Skipping due to required options
            if (strpos($target, '\\Between')) {
                continue;
            }

            // Skipping due to required options
            if (strpos($target, '\\Db\\')) {
                continue;
            }

            // Skipping due to required options
            if (strpos($target, '\\File\\ExcludeExtension')) {
                continue;
            }

            // Skipping due to required options
            if (strpos($target, '\\File\\Extension')) {
                continue;
            }

            // Skipping due to required options
            if (strpos($target, '\\File\\FilesSize')) {
                continue;
            }

            // Skipping due to required options
            if (strpos($target, '\\Regex')) {
                continue;
            }

            yield $alias => [$alias, $target];
        }
    }

    /**
     * Provided only for compatibility with the lowest integration tests from Laminas\ServiceManager (v2)
     */
    private function setExpectedException(string $exceptionClassName) : void
    {
        $this->expectException($exceptionClassName);
    }
}
