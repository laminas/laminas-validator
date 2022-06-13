<?php

namespace LaminasTest\Validator;

use Exception;
use Interop\Container\ContainerInterface; // phpcs:ignore
use Laminas\ServiceManager\Exception\InvalidServiceException;
use Laminas\ServiceManager\ServiceManager;
use Laminas\Validator\AbstractValidator;
use Laminas\Validator\Exception\RuntimeException;
use Laminas\Validator\NotEmpty;
use Laminas\Validator\ValidatorInterface;
use Laminas\Validator\ValidatorPluginManager;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

use function get_class;
use function sprintf;

/**
 * @group      Laminas_Validator
 */
class ValidatorPluginManagerTest extends TestCase
{
    use ProphecyTrait;

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
            $this->validators->setService('test', $this);
        } catch (InvalidServiceException $e) {
            $this->assertStringContainsString(ValidatorInterface::class, $e->getMessage());
        } catch (RuntimeException $e) {
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
        } catch (InvalidServiceException $e) {
            $this->assertStringContainsString(ValidatorInterface::class, $e->getMessage());
        } catch (RuntimeException $e) {
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
        $validator = $this->validators->get('explode');
        $this->assertSame($this->validators, $validator->getValidatorPluginManager());
    }
}
