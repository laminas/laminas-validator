<?php

declare(strict_types=1);

namespace LaminasTest\Validator\Translator;

// phpcs:disable WebimpressCodingStandard.PHP.CorrectClassNameCase

use ArrayAccess;
use ArrayObject;
use Interop\Container\ContainerInterface;
use Laminas\I18n\Translator\LoaderPluginManager;
use Laminas\I18n\Translator\Translator as I18nTranslator;
use Laminas\I18n\Translator\TranslatorInterface;
use Laminas\ServiceManager\ServiceManager;
use Laminas\Validator\Translator\DummyTranslator;
use Laminas\Validator\Translator\Translator;
use Laminas\Validator\Translator\TranslatorFactory;
use Locale;
use PHPUnit\Framework\Constraint\IsInstanceOf;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

use function extension_loaded;

class TranslatorFactoryTest extends TestCase
{
    public function testFactoryReturnsTranslatorDecoratingTranslatorInterfaceServiceWhenPresent(): void
    {
        $translator = $this->createMock(TranslatorInterface::class);

        $container = $this->createMock(ServiceManager::class);
        $container
            ->expects(self::once())
            ->method('has')
            ->with(TranslatorInterface::class)
            ->willReturn(true);
        $container
            ->expects(self::once())
            ->method('get')
            ->with(TranslatorInterface::class)
            ->willReturn($translator);

        self::assertInstanceOf(ContainerInterface::class, $container);

        $factory = new TranslatorFactory();
        $test    = $factory($container);

        $this->assertInstanceOf(Translator::class, $test);

        $prop = new ReflectionProperty($test, 'translator');
        $this->assertSame($translator, $prop->getValue($test));
    }

    /** @psalm-return array<string, array{0: class-string}> */
    public static function expectedTranslatorProvider(): array
    {
        return extension_loaded('intl')
            ? ['intl-loaded' => [I18nTranslator::class]]
            : ['no-intl-loaded' => [DummyTranslator::class]];
    }

    /**
     * @dataProvider expectedTranslatorProvider
     * @psalm-param class-string $expected
     */
    public function testFactoryReturnsTranslatorDecoratingDefaultTranslatorWhenNoConfigPresent(
        string $expected
    ): void {
        $container = $this->createMock(ServiceManager::class);
        $container
            ->expects(self::exactly(2))
            ->method('has')
            ->willReturnMap(
                [
                    [TranslatorInterface::class, false],
                    ['config', false],
                ]
            );
        $container
            ->expects(self::never())
            ->method('get');

        self::assertInstanceOf(ContainerInterface::class, $container);

        $factory = new TranslatorFactory();
        $test    = $factory($container);

        $this->assertInstanceOf(Translator::class, $test);

        $prop = new ReflectionProperty($test, 'translator');
        $this->assertInstanceOf($expected, $prop->getValue($test));
    }

    /**
     * @dataProvider expectedTranslatorProvider
     * @psalm-param class-string $expected
     */
    public function testFactoryReturnsMvcDecoratorDecoratingDefaultTranslatorWhenNoTranslatorConfigPresent(
        string $expected
    ): void {
        $container = $this->createMock(ServiceManager::class);
        $container
            ->expects(self::exactly(2))
            ->method('has')
            ->willReturnMap(
                [
                    [TranslatorInterface::class, false],
                    ['config', true],
                ]
            );
        $container
            ->expects(self::once())
            ->method('get')
            ->with('config')
            ->willReturn([]);

        self::assertInstanceOf(ContainerInterface::class, $container);

        $factory = new TranslatorFactory();
        $test    = $factory($container);

        $this->assertInstanceOf(Translator::class, $test);

        $prop = new ReflectionProperty($test, 'translator');
        $this->assertInstanceOf($expected, $prop->getValue($test));
    }

    public function testFactoryReturnsMvcDecoratorDecoratingDummyTranslatorWhenTranslatorConfigIsFalse(): void
    {
        $container = $this->createMock(ServiceManager::class);
        $container
            ->expects(self::exactly(2))
            ->method('has')
            ->willReturnMap(
                [
                    [TranslatorInterface::class, false],
                    ['config', true],
                ]
            );
        $container
            ->expects(self::once())
            ->method('get')
            ->with('config')
            ->willReturn(['translator' => false]);

        self::assertInstanceOf(ContainerInterface::class, $container);

        $factory = new TranslatorFactory();
        $test    = $factory($container);

        $this->assertInstanceOf(Translator::class, $test);

        $prop = new ReflectionProperty($test, 'translator');
        $this->assertInstanceOf(DummyTranslator::class, $prop->getValue($test));
    }

    /**
     * @dataProvider expectedTranslatorProvider
     * @psalm-param class-string $expected
     */
    public function testFactoryReturnsMvcDecoratorDecoratingDefaultTranslatorWhenEmptyTranslatorConfigPresent(
        string $expected
    ): void {
        $container = $this->createMock(ServiceManager::class);
        $container
            ->expects(self::exactly(2))
            ->method('has')
            ->willReturnMap(
                [
                    [TranslatorInterface::class, false],
                    ['config', true],
                ]
            );
        $container
            ->expects(self::once())
            ->method('get')
            ->with('config')
            ->willReturn(['translator' => []]);

        self::assertInstanceOf(ContainerInterface::class, $container);

        $factory = new TranslatorFactory();
        $test    = $factory($container);

        $this->assertInstanceOf(Translator::class, $test);

        $prop = new ReflectionProperty($test, 'translator');
        $this->assertInstanceOf($expected, $prop->getValue($test));
    }

    /** @psalm-return array<string, array{0: array<string, mixed>, 1: class-string}> */
    public static function invalidTranslatorConfig(): array
    {
        $expectedTranslator = extension_loaded('intl')
            ? I18nTranslator::class
            : DummyTranslator::class;

        return [
            'null'    => [['translator' => null], $expectedTranslator],
            'true'    => [['translator' => true], $expectedTranslator],
            'zero'    => [['translator' => 0], $expectedTranslator],
            'int'     => [['translator' => 1], $expectedTranslator],
            'float-0' => [['translator' => 0.0], $expectedTranslator],
            'float'   => [['translator' => 1.1], $expectedTranslator],
            'string'  => [['translator' => 'invalid'], $expectedTranslator],
            'object'  => [['translator' => (object) ['translator' => 'invalid']], $expectedTranslator],
        ];
    }

    /**
     * @param array<string, mixed> $config
     * @psalm-param class-string $expected
     * @dataProvider invalidTranslatorConfig
     */
    public function testFactoryReturnsDecoratorDecoratingDefaultTranslatorWithInvalidTranslatorConfig(
        $config,
        $expected
    ): void {
        $container = $this->createMock(ServiceManager::class);
        $container
            ->expects(self::exactly(2))
            ->method('has')
            ->willReturnMap(
                [
                    [TranslatorInterface::class, false],
                    ['config', true],
                ]
            );
        $container
            ->expects(self::once())
            ->method('get')
            ->with('config')
            ->willReturn($config);

        self::assertInstanceOf(ContainerInterface::class, $container);

        $factory = new TranslatorFactory();
        $test    = $factory($container);

        $this->assertInstanceOf(Translator::class, $test);

        $prop = new ReflectionProperty($test, 'translator');
        $this->assertInstanceOf($expected, $prop->getValue($test));
    }

    /**
     * @psalm-return array<non-empty-string,array{0:array<string,mixed>|ArrayAccess<string,mixed>}>
     */
    public static function validTranslatorConfig(): array
    {
        $locale = Locale::getDefault() === 'en-US' ? 'de-DE' : Locale::getDefault();
        $config = [
            'locale'                => $locale,
            'event_manager_enabled' => true,
        ];

        return [
            'array'       => [$config],
            'traversable' => [new ArrayObject($config)],
        ];
    }

    /**
     * @requires extension intl
     * @dataProvider validTranslatorConfig
     * @param array<string,mixed>|ArrayAccess<string,mixed> $config
     */
    public function testFactoryReturnsConfiguredTranslatorWhenValidConfigIsPresent($config): void
    {
        $container = $this->createMock(ServiceManager::class);
        $container
            ->expects(self::exactly(3))
            ->method('has')
            ->willReturnMap(
                [
                    [TranslatorInterface::class, false],
                    ['config', true],
                    ['TranslatorPluginManager', false],
                ]
            );
        $container
            ->expects(self::once())
            ->method('get')
            ->with('config')
            ->willReturn(['translator' => $config]);
        $container
            ->expects(self::once())
            ->method('setService')
            ->with(TranslatorInterface::class, new IsInstanceOf(I18nTranslator::class));

        self::assertInstanceOf(ContainerInterface::class, $container);

        $factory = new TranslatorFactory();
        $test    = $factory($container);

        $this->assertInstanceOf(Translator::class, $test);

        $prop      = new ReflectionProperty($test, 'translator');
        $decorated = $prop->getValue($test);

        $this->assertInstanceOf(I18nTranslator::class, $decorated);
        $locale = $config['locale'] ?? null;
        self::assertIsString($locale);
        $this->assertEquals($locale, $decorated->getLocale());
        $this->assertTrue($decorated->isEventManagerEnabled());
    }

    /**
     * @param array<string,mixed>|ArrayAccess<string,mixed> $config
     * @requires extension intl
     * @dataProvider validTranslatorConfig
     */
    public function testFactoryReturnsConfiguredTranslatorInjectedWithTranslatorPluginManagerWhenValidConfigIsPresent(
        $config
    ): void {
        $loaders = $this->createMock(LoaderPluginManager::class);

        $container = $this->createMock(ServiceManager::class);
        $container
            ->expects(self::exactly(3))
            ->method('has')
            ->willReturnMap(
                [
                    [TranslatorInterface::class, false],
                    ['config', true],
                    ['TranslatorPluginManager', true],
                ]
            );
        $container
            ->expects(self::exactly(2))
            ->method('get')
            ->willReturnMap(
                [
                    ['config', ['translator' => $config]],
                    ['TranslatorPluginManager', $loaders],
                ]
            );
        $container
            ->expects(self::once())
            ->method('setService')
            ->with(TranslatorInterface::class, new IsInstanceOf(I18nTranslator::class));

        self::assertInstanceOf(ContainerInterface::class, $container);

        $factory = new TranslatorFactory();
        $test    = $factory($container);

        $this->assertInstanceOf(Translator::class, $test);

        $prop      = new ReflectionProperty($test, 'translator');
        $decorated = $prop->getValue($test);

        $this->assertInstanceOf(I18nTranslator::class, $decorated);
        $this->assertEquals($config['locale'], $decorated->getLocale());
        $this->assertTrue($decorated->isEventManagerEnabled());
        $this->assertSame($loaders, $decorated->getPluginManager());
    }
}
