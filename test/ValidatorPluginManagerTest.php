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
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Container\ContainerInterface;

use function assert;
use function get_class;
use function sprintf;

/**
 * @group      Laminas_Validator
 */
class ValidatorPluginManagerTest extends TestCase
{
    use ProphecyTrait;

    private ValidatorPluginManager $validators;

    protected function setUp(): void
    {
        $this->validators = new ValidatorPluginManager(new ServiceManager());
    }

    public function testAllowsInjectingTranslator(): void
    {
        $translator = $this->prophesize(TestAsset\Translator::class)->reveal();

        $container = $this->prophesize(ContainerInterface::class);
        $container->has('MvcTranslator')->willReturn(true);
        $container->get('MvcTranslator')->willReturn($translator);

        $validators = new ValidatorPluginManager($container->reveal());

        $validator = $validators->get(NotEmpty::class);
        self::assertInstanceOf(AbstractValidator::class, $validator);
        $this->assertEquals($translator, $validator->getTranslator());
    }

    public function testNoTranslatorInjectedWhenTranslatorIsNotPresent(): void
    {
        $container = $this->prophesize(ContainerInterface::class);
        $container->has('MvcTranslator')->willReturn(false);

        $validators = new ValidatorPluginManager($container->reveal());

        $validator = $validators->get(NotEmpty::class);
        self::assertInstanceOf(AbstractValidator::class, $validator);
        $this->assertNull($validator->getTranslator());
    }

    public function testRegisteringInvalidValidatorRaisesException(): void
    {
        try {
            /** @psalm-suppress InvalidArgument */
            $this->validators->setService('test', $this);
        } catch (InvalidServiceException | RuntimeException $e) {
            $this->assertStringContainsString(ValidatorInterface::class, $e->getMessage());
        } catch (Exception $e) {
            $this->fail(sprintf(
                'Unexpected exception of type "%s" when testing for invalid validator types',
                get_class($e)
            ));
        }
    }

    public function testLoadingInvalidValidatorRaisesException(): void
    {
        $this->validators->setInvokableClass('test', static::class);
        try {
            $this->validators->get('test');
        } catch (InvalidServiceException | RuntimeException $e) {
            $this->assertStringContainsString(ValidatorInterface::class, $e->getMessage());
        } catch (Exception $e) {
            $this->fail(sprintf(
                'Unexpected exception of type "%s" when testing for invalid validator types',
                get_class($e)
            ));
        }
    }

    public function testInjectedValidatorPluginManager(): void
    {
        $validator = $this->validators->get(Explode::class);
        assert($validator instanceof Explode);
        $this->assertSame($this->validators, $validator->getValidatorPluginManager());
    }
}
