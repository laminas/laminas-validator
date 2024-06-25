<?php

declare(strict_types=1);

namespace LaminasTest\Validator;

use Laminas\ServiceManager\ServiceManager;
use Laminas\ServiceManager\Test\CommonPluginManagerTrait;
use Laminas\Validator\DateComparison;
use Laminas\Validator\Exception\RuntimeException;
use Laminas\Validator\NumberComparison;
use Laminas\Validator\ValidatorInterface;
use Laminas\Validator\ValidatorPluginManager;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

use function assert;
use function is_string;
use function method_exists;
use function strpos;

final class ValidatorPluginManagerCompatibilityTest extends TestCase
{
    use CommonPluginManagerTrait;

    protected static function getPluginManager(): ValidatorPluginManager
    {
        return new ValidatorPluginManager(new ServiceManager());
    }

    protected function getV2InvalidPluginException(): string
    {
        return RuntimeException::class;
    }

    protected function getInstanceOf(): string
    {
        return ValidatorInterface::class;
    }

    /** @return array<string, array{0: string, 1: string}> */
    public static function aliasProvider(): array
    {
        $out               = [];
        $pluginManager     = self::getPluginManager();
        $isV2PluginManager = method_exists($pluginManager, 'validatePlugin');

        $r       = new ReflectionProperty($pluginManager, 'aliases');
        $aliases = $r->getValue($pluginManager);
        self::assertIsArray($aliases);

        foreach ($aliases as $alias => $target) {
            assert(is_string($target));
            assert(is_string($alias));

            // Skipping due to required options
            if (strpos($target, '\\Barcode') !== false) {
                continue;
            }

            // Skipping due to required options
            if (strpos($target, '\\Between') !== false) {
                continue;
            }

            // Skipping on v2 releases of service manager
            if ($isV2PluginManager && strpos($target, '\\BusinessIdentifierCode') !== false) {
                continue;
            }

            // Skipping due to required options
            if (strpos($target, '\\Db\\') !== false) {
                continue;
            }

            // Skipping due to required options
            if (strpos($target, '\\File\\ExcludeExtension') !== false) {
                continue;
            }

            // Skipping due to required options
            if (strpos($target, '\\File\\Extension') !== false) {
                continue;
            }

            // Skipping due to required options
            if (strpos($target, '\\File\\FilesSize') !== false) {
                continue;
            }

            // Skipping due to required options
            if (strpos($target, '\\Regex') !== false) {
                continue;
            }

            // Skipping due to required options
            if ($target === DateComparison::class) {
                continue;
            }

            // Skipping due to required options
            if ($target === NumberComparison::class) {
                continue;
            }

            $out[$alias] = [$alias, $target];
        }

        return $out;
    }
}
