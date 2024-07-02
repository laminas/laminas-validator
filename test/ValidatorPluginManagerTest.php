<?php

declare(strict_types=1);

namespace LaminasTest\Validator;

use Exception;
use Laminas\ServiceManager\Exception\InvalidServiceException;
use Laminas\ServiceManager\ServiceManager;
use Laminas\Validator\Exception\RuntimeException;
use Laminas\Validator\ValidatorInterface;
use Laminas\Validator\ValidatorPluginManager;
use Laminas\Validator\ValidatorPluginManagerAwareInterface;
use LaminasTest\Validator\TestAsset\InMemoryContainer;
use PHPUnit\Framework\TestCase;

use function assert;
use function is_scalar;
use function sprintf;

final class ValidatorPluginManagerTest extends TestCase
{
    private ValidatorPluginManager $validators;

    protected function setUp(): void
    {
        parent::setUp();

        $this->validators = new ValidatorPluginManager(new ServiceManager());
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
                $e::class,
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
                $e::class,
            ));
        }
    }

    public function testInjectedValidatorPluginManager(): void
    {
        $validator = new class implements ValidatorInterface, ValidatorPluginManagerAwareInterface
        {
            private ?ValidatorPluginManager $plugins = null;

            public function isValid(mixed $value): bool
            {
                return is_scalar($value);
            }

            public function getMessages(): array
            {
                return [];
            }

            public function setValidatorPluginManager(ValidatorPluginManager $pluginManager): void
            {
                $this->plugins = $pluginManager;
            }

            public function getValidatorPluginManager(): ValidatorPluginManager
            {
                assert($this->plugins !== null);

                return $this->plugins;
            }
        };

        $plugins = new ValidatorPluginManager(new ServiceManager(), [
            'factories' => [
                'test' => static fn () => $validator,
            ],
        ]);

        $retrieved = $plugins->get('test');
        self::assertSame($validator, $retrieved);

        self::assertSame($plugins, $validator->getValidatorPluginManager());
    }
}
