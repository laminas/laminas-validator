<?php

declare(strict_types=1);

namespace LaminasTest\Validator;

use Laminas\ServiceManager\AbstractSingleInstancePluginManager;
use Laminas\ServiceManager\ServiceManager;
use Laminas\ServiceManager\Test\CommonPluginManagerTrait;
use Laminas\Validator\Barcode;
use Laminas\Validator\Bitwise;
use Laminas\Validator\Callback;
use Laminas\Validator\DateComparison;
use Laminas\Validator\Explode;
use Laminas\Validator\File\ExcludeExtension;
use Laminas\Validator\File\ExcludeMimeType;
use Laminas\Validator\File\Extension;
use Laminas\Validator\File\FilesSize;
use Laminas\Validator\File\Hash;
use Laminas\Validator\File\ImageSize;
use Laminas\Validator\File\MimeType;
use Laminas\Validator\File\Size;
use Laminas\Validator\File\WordCount;
use Laminas\Validator\InArray;
use Laminas\Validator\IsInstanceOf;
use Laminas\Validator\NumberComparison;
use Laminas\Validator\Regex;
use Laminas\Validator\ValidatorInterface;
use Laminas\Validator\ValidatorPluginManager;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

use function assert;
use function in_array;
use function is_string;

/** @psalm-import-type ServiceManagerConfiguration from ServiceManager */
final class ValidatorPluginManagerCompatibilityTest extends TestCase
{
    use CommonPluginManagerTrait;

    private const SKIP_VALIDATORS = [
        Barcode::class,
        ExcludeExtension::class,
        Extension::class,
        FilesSize::class,
        Regex::class,
        Bitwise::class,
        Explode::class,
        Callback::class,
        DateComparison::class,
        NumberComparison::class,
        IsInstanceOf::class,
        InArray::class,
        MimeType::class,
        ExcludeMimeType::class,
        Size::class,
        WordCount::class,
        ImageSize::class,
        Hash::class,
    ];

    /**
     * Returns the plugin manager to test
     *
     * @param ServiceManagerConfiguration $config
     */
    protected static function getPluginManager(array $config = []): AbstractSingleInstancePluginManager
    {
        return new ValidatorPluginManager(new ServiceManager(), $config);
    }

    protected function getInstanceOf(): string
    {
        return ValidatorInterface::class;
    }

    /** @return array<string, array{0: string, 1: string}> */
    public static function aliasProvider(): array
    {
        $class  = new ReflectionClass(ValidatorPluginManager::class);
        $config = $class->getConstant('DEFAULT_CONFIGURATION');
        self::assertIsArray($config);
        self::assertIsArray($config['aliases'] ?? null);

        $out = [];

        foreach ($config['aliases'] as $alias => $target) {
            assert(is_string($target));
            assert(is_string($alias));

            // Skipping due to required options
            if (in_array($target, self::SKIP_VALIDATORS, true)) {
                continue;
            }

            $out[$alias] = [$alias, $target];
        }

        return $out;
    }
}
