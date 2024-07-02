<?php

declare(strict_types=1);

namespace LaminasTest\Validator;

use InvalidArgumentException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Laminas\ServiceManager\ServiceManager;
use Laminas\Validator\AbstractValidator;
use Laminas\Validator\Digits;
use Laminas\Validator\StaticValidator;
use Laminas\Validator\StringLength;
use Laminas\Validator\ValidatorInterface;
use Laminas\Validator\ValidatorPluginManager;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

use function current;
use function strlen;

final class StaticValidatorTest extends TestCase
{
    /**
     * Creates a new validation object for each test method
     */
    protected function setUp(): void
    {
        parent::setUp();

        StaticValidator::setPluginManager(null);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        AbstractValidator::setMessageLength(-1);
    }

    public function testMaximumErrorMessageLength(): void
    {
        $validator = new StringLength([
            'messages' => [
                StringLength::INVALID => 'One, two, buckle my shoe',
            ],
        ]);

        self::assertSame(-1, AbstractValidator::getMessageLength());

        AbstractValidator::setMessageLength(10);

        self::assertSame(10, AbstractValidator::getMessageLength());

        self::assertFalse($validator->isValid(123));

        $messages = $validator->getMessages();

        self::assertArrayHasKey(StringLength::INVALID, $messages);
        self::assertSame('One, tw...', $messages[StringLength::INVALID]);
    }

    public function testSetGetMessageLengthLimitation(): void
    {
        AbstractValidator::setMessageLength(5);

        self::assertSame(5, AbstractValidator::getMessageLength());

        $valid = new Digits();

        self::assertFalse($valid->isValid('foo'));

        $message = current($valid->getMessages());

        self::assertLessThanOrEqual(5, strlen($message));
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
     *     0: mixed,
     *     1: class-string<ValidatorInterface>,
     *     2: array<string, int>,
     *     3: bool
     * }>
     */
    public static function parameterizedData(): array
    {
        return [
            'valid-length'   => ['foo', StringLength::class, ['min' => 1, 'max' => 10], true],
            'invalid-length' => ['foo', StringLength::class, ['min' => 5, 'max' => 10], false],
        ];
    }

    /**
     * @param class-string<ValidatorInterface> $validator
     */
    #[DataProvider('parameterizedData')]
    public function testExecuteValidWithParameters(
        mixed $value,
        string $validator,
        array $options,
        bool $expected
    ): void {
        self::assertSame($expected, StaticValidator::execute($value, $validator, $options));
    }

    /**
     * @psalm-return array<string, array{0: mixed, 1: class-string<ValidatorInterface>, 2: int[]}>
     */
    public static function invalidParameterizedData(): array
    {
        return [
            'invalid-options' => ['foo', StringLength::class, [5, 10]],
        ];
    }

    /**
     * @param class-string<ValidatorInterface> $validator
     */
    #[DataProvider('invalidParameterizedData')]
    public function testExecuteRaisesExceptionForIndexedOptionsArray(
        mixed $value,
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
     */
    #[Group('Laminas-2724')]
    public function testStaticFactoryClassNotFound(): void
    {
        $this->expectException(ServiceNotFoundException::class);

        /** @psalm-suppress ArgumentTypeCoercion, UndefinedClass */
        StaticValidator::execute('1234', 'UnknownValidator');
    }
}
