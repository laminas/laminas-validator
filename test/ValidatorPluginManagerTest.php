<?php

declare(strict_types=1);

namespace LaminasTest\Validator;

use Exception;
use Laminas\ServiceManager\Exception\InvalidServiceException;
use Laminas\ServiceManager\ServiceManager;
use Laminas\Validator\AbstractValidator;
use Laminas\Validator\Exception\RuntimeException;
use Laminas\Validator\Explode;
use Laminas\Validator\NotEmpty;
use Laminas\Validator\ValidatorInterface;
use Laminas\Validator\ValidatorPluginManager;
use LaminasTest\Validator\TestAsset\InMemoryContainer;
use LaminasTest\Validator\TestAsset\Translator;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

use function assert;
use function sprintf;

final class ValidatorPluginManagerTest extends TestCase
{
    private ValidatorPluginManager $validators;

    protected function setUp(): void
    {
        parent::setUp();

        $this->validators = new ValidatorPluginManager(new ServiceManager());
    }

    public function testAllowsInjectingTranslator(): void
    {
        $translator = $this->createMock(Translator::class);

        $container = $this->createMock(ContainerInterface::class);

        $container
            ->expects(self::once())
            ->method('has')
            ->with('MvcTranslator')
            ->willReturn(true);

        $container
            ->expects(self::once())
            ->method('get')
            ->with('MvcTranslator')
            ->willReturn($translator);

        $validators = new ValidatorPluginManager($container);

        $validator = $validators->get(NotEmpty::class);

        self::assertInstanceOf(AbstractValidator::class, $validator);
        self::assertEquals($translator, $validator->getTranslator());
    }

    public function testNoTranslatorInjectedWhenTranslatorIsNotPresent(): void
    {
        $container = $this->createMock(ContainerInterface::class);

        $container
            ->expects(self::once())
            ->method('has')
            ->with('MvcTranslator')
            ->willReturn(false);

        $container
            ->expects(self::never())
            ->method('get')
            ->with('MvcTranslator');

        $validators = new ValidatorPluginManager($container);

        $validator = $validators->get(NotEmpty::class);

        self::assertInstanceOf(AbstractValidator::class, $validator);
        self::assertNull($validator->getTranslator());
    }

    public function testRegisteringInvalidValidatorRaisesException(): void
    {
        try {
            /** @psalm-suppress InvalidArgument */
            $this->validators->setService('test', $this);
        } catch (InvalidServiceException | RuntimeException $e) {
            self::assertStringContainsString(ValidatorInterface::class, $e->getMessage());
        } catch (Exception $e) {
            self::fail(sprintf(
                'Unexpected exception of type "%s" when testing for invalid validator types',
                $e::class
            ));
        }
    }

    public function testLoadingInvalidValidatorRaisesException(): void
    {
        $this->validators->setInvokableClass('test', InMemoryContainer::class);

        try {
            $this->validators->get('test');
            self::fail('An exception should have been thrown');
        } catch (InvalidServiceException | RuntimeException $e) {
            self::assertStringContainsString(ValidatorInterface::class, $e->getMessage());
        } catch (Exception $e) {
            self::fail(sprintf(
                'Unexpected exception of type "%s" when testing for invalid validator types',
                $e::class
            ));
        }
    }

    public function testInjectedValidatorPluginManager(): void
    {
        $validator = $this->validators->get(Explode::class);

        assert($validator instanceof Explode);

        self::assertSame($this->validators, $validator->getValidatorPluginManager());
    }
}
