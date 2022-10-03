<?php

declare(strict_types=1);

namespace LaminasTest\Validator;

use InvalidArgumentException;
use Laminas\I18n\Validator\Alpha;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Laminas\ServiceManager\ServiceManager;
use Laminas\Validator\AbstractValidator;
use Laminas\Validator\Between;
use Laminas\Validator\StaticValidator;
use Laminas\Validator\ValidatorInterface;
use Laminas\Validator\ValidatorPluginManager;
use LaminasTest\Validator\TestAsset\ArrayTranslator;
use LaminasTest\Validator\TestAsset\Translator;
use PHPUnit\Framework\TestCase;

use function current;
use function extension_loaded;
use function strlen;

/**
 * @group Laminas_Validator
 * @covers \Laminas\Validator\StaticValidator
 */
final class StaticValidatorTest extends TestCase
{
    private Alpha $validator;

    /**
     * Creates a new validation object for each test method
     */
    protected function setUp(): void
    {
        parent::setUp();

        AbstractValidator::setDefaultTranslator(null);
        StaticValidator::setPluginManager(null);
        $this->validator = new Alpha();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        AbstractValidator::setDefaultTranslator(null);
        AbstractValidator::setMessageLength(-1);
    }

    public function testCanSetGlobalDefaultTranslator(): void
    {
        $translator = new Translator();
        AbstractValidator::setDefaultTranslator($translator);

        self::assertSame($translator, AbstractValidator::getDefaultTranslator());
    }

    public function testGlobalDefaultTranslatorUsedWhenNoLocalTranslatorSet(): void
    {
        $this->testCanSetGlobalDefaultTranslator();

        self::assertSame(AbstractValidator::getDefaultTranslator(), $this->validator->getTranslator());
    }

    public function testLocalTranslatorPreferredOverGlobalTranslator(): void
    {
        $this->testCanSetGlobalDefaultTranslator();
        $translator = new Translator();
        $this->validator->setTranslator($translator);

        self::assertNotSame(AbstractValidator::getDefaultTranslator(), $this->validator->getTranslator());
    }

    public function testMaximumErrorMessageLength(): void
    {
        if (! extension_loaded('intl')) {
            self::markTestSkipped('ext/intl not enabled');
        }

        self::assertSame(-1, AbstractValidator::getMessageLength());

        AbstractValidator::setMessageLength(10);

        self::assertSame(10, AbstractValidator::getMessageLength());

        $loader               = new ArrayTranslator();
        $loader->translations = [
            'Invalid type given. String expected' => 'This is the translated message for %value%',
        ];
        $translator           = new Translator();
        $translator->getPluginManager()->setService('default', $loader);
        $translator->addTranslationFile('default', null);

        $this->validator->setTranslator($translator);

        self::assertFalse($this->validator->isValid(123));

        $messages = $this->validator->getMessages();

        self::assertArrayHasKey(Alpha::INVALID, $messages);
        self::assertSame('This is...', $messages[Alpha::INVALID]);
    }

    public function testSetGetMessageLengthLimitation(): void
    {
        AbstractValidator::setMessageLength(5);

        self::assertSame(5, AbstractValidator::getMessageLength());

        $valid = new Between(1, 10);

        self::assertFalse($valid->isValid(24));

        $message = current($valid->getMessages());

        self::assertLessThanOrEqual(5, strlen($message));
    }

    public function testSetGetDefaultTranslator(): void
    {
        $translator = new Translator();
        AbstractValidator::setDefaultTranslator($translator);

        self::assertSame($translator, AbstractValidator::getDefaultTranslator());
    }

    public function testLazyLoadsValidatorPluginManagerByDefault(): void
    {
        $plugins = StaticValidator::getPluginManager();

        self::assertInstanceOf(ValidatorPluginManager::class, $plugins);
    }

    public function testCanSetCustomPluginManager(): void
    {
        $plugins = new ValidatorPluginManager($this->getMockBuilder(ServiceManager::class)->getMock());
        StaticValidator::setPluginManager($plugins);

        self::assertSame($plugins, StaticValidator::getPluginManager());
    }

    public function testPassingNullWhenSettingPluginManagerResetsPluginManager(): void
    {
        $plugins = new ValidatorPluginManager($this->getMockBuilder(ServiceManager::class)->getMock());
        StaticValidator::setPluginManager($plugins);

        self::assertSame($plugins, StaticValidator::getPluginManager());

        StaticValidator::setPluginManager(null);

        self::assertNotSame($plugins, StaticValidator::getPluginManager());
    }

    /**
     * @psalm-return array<string, array{
     *     0: int,
     *     1: class-string<ValidatorInterface>,
     *     2: array<string, int>,
     *     3: bool
     * }>
     */
    public function parameterizedData(): array
    {
        return [
            'valid-positive-range'   => [5, Between::class, ['min' => 1, 'max' => 10], true],
            'valid-negative-range'   => [-5, Between::class, ['min' => -10, 'max' => -1], true],
            'invalid-positive-range' => [-5, Between::class, ['min' => 1, 'max' => 10], false],
            'invalid-negative-range' => [5, Between::class, ['min' => -10, 'max' => -1], false],
        ];
    }

    /**
     * @dataProvider parameterizedData
     * @param class-string<ValidatorInterface> $validator
     */
    public function testExecuteValidWithParameters(
        int $value,
        string $validator,
        array $options,
        bool $expected
    ): void {
        self::assertSame($expected, StaticValidator::execute($value, $validator, $options));
    }

    /**
     * @psalm-return array<string, array{0: int, 1: class-string<ValidatorInterface>, 2: int[]}>
     */
    public function invalidParameterizedData(): array
    {
        return [
            'positive-range' => [5, Between::class, [1, 10]],
            'negative-range' => [-5, Between::class, [-10, -1]],
        ];
    }

    /**
     * @dataProvider invalidParameterizedData
     * @param class-string<ValidatorInterface> $validator
     */
    public function testExecuteRaisesExceptionForIndexedOptionsArray(
        int $value,
        string $validator,
        array $options
    ): void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('options');

        StaticValidator::execute($value, $validator, $options);
    }

    /**
     * Ensures that if we specify a validator class basename that doesn't
     * exist in the namespace, is() throws an exception.
     *
     * Refactored to conform with Laminas-2724.
     *
     * @group Laminas-2724
     */
    public function testStaticFactoryClassNotFound(): void
    {
        $this->expectException(ServiceNotFoundException::class);

        /** @psalm-suppress ArgumentTypeCoercion, UndefinedClass */
        StaticValidator::execute('1234', 'UnknownValidator');
    }
}
