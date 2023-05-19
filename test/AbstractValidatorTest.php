<?php

declare(strict_types=1);

namespace LaminasTest\Validator;

use Laminas\Validator\AbstractValidator;
use Laminas\Validator\EmailAddress;
use Laminas\Validator\Exception\InvalidArgumentException;
use Laminas\Validator\Hostname;
use LaminasTest\Validator\TestAsset\ArrayTranslator;
use LaminasTest\Validator\TestAsset\ConcreteValidator;
use LaminasTest\Validator\TestAsset\Translator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use stdClass;

use function extension_loaded;
use function reset;
use function sprintf;
use function var_export;

final class AbstractValidatorTest extends TestCase
{
    private AbstractValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = new ConcreteValidator();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        AbstractValidator::setDefaultTranslator(null, 'default');
    }

    public function testTranslatorNullByDefault(): void
    {
        self::assertNull($this->validator->getTranslator());
    }

    public function testCanSetTranslator(): void
    {
        $translator = new Translator();
        $this->validator->setTranslator($translator);

        self::assertSame($translator, $this->validator->getTranslator());
    }

    public function testCanSetTranslatorToNull(): void
    {
        $this->testCanSetTranslator();
        $this->validator->setTranslator(null);

        self::assertNull($this->validator->getTranslator());
    }

    public function testErrorMessagesAreTranslatedWhenTranslatorPresent(): void
    {
        if (! extension_loaded('intl')) {
            self::markTestSkipped('ext/intl not enabled');
        }

        $loader               = new ArrayTranslator();
        $loader->translations = [
            '%value% was passed' => 'This is the translated message for %value%',
        ];
        $translator           = new Translator();
        $translator->getPluginManager()->setService('default', $loader);
        $translator->addTranslationFile('default', null);

        $this->validator->setTranslator($translator);

        self::assertFalse($this->validator->isValid('bar'));

        $messages = $this->validator->getMessages();

        self::assertArrayHasKey('fooMessage', $messages);
        self::assertStringContainsString('bar', $messages['fooMessage'], var_export($messages, true));
        self::assertStringContainsString('This is the translated message for ', $messages['fooMessage']);
    }

    public function testObscureValueFlagFalseByDefault(): void
    {
        self::assertFalse($this->validator->isValueObscured());
    }

    public function testCanSetValueObscuredFlag(): void
    {
        $this->testObscureValueFlagFalseByDefault();

        $this->validator->setValueObscured(true);

        self::assertTrue($this->validator->isValueObscured());

        $this->validator->setValueObscured(false);

        self::assertFalse($this->validator->isValueObscured());
    }

    public function testValueIsObfuscatedWheObscureValueFlagIsTrue(): void
    {
        $this->validator->setValueObscured(true);

        self::assertFalse($this->validator->isValid('foobar'));

        $messages = $this->validator->getMessages();

        self::assertTrue(isset($messages['fooMessage']));

        $message = $messages['fooMessage'];

        self::assertStringNotContainsString('foobar', $message);
        self::assertStringContainsString('******', $message);
    }

    #[Group('Laminas-4463')]
    public function testDoesNotFailOnObjectInput(): void
    {
        self::assertFalse($this->validator->isValid(new stdClass()));

        $messages = $this->validator->getMessages();

        self::assertArrayHasKey('fooMessage', $messages);
    }

    public function testTranslatorEnabledPerDefault(): void
    {
        $translator = new Translator();
        $this->validator->setTranslator($translator);

        self::assertTrue($this->validator->isTranslatorEnabled());
    }

    public function testCanDisableTranslator(): void
    {
        if (! extension_loaded('intl')) {
            self::markTestSkipped('ext/intl not enabled');
        }

        $loader               = new ArrayTranslator();
        $loader->translations = [
            '%value% was passed' => 'This is the translated message for %value%',
        ];
        $translator           = new Translator();
        $translator->getPluginManager()->setService('default', $loader);
        $translator->addTranslationFile('default', null);
        $this->validator->setTranslator($translator);

        self::assertFalse($this->validator->isValid('bar'));

        $messages = $this->validator->getMessages();

        self::assertArrayHasKey('fooMessage', $messages);
        self::assertStringContainsString('bar', $messages['fooMessage']);
        self::assertStringContainsString('This is the translated message for ', $messages['fooMessage']);

        $this->validator->setTranslatorEnabled(false);

        self::assertFalse($this->validator->isTranslatorEnabled());
        self::assertFalse($this->validator->isValid('bar'));

        $messages = $this->validator->getMessages();

        self::assertArrayHasKey('fooMessage', $messages);
        self::assertStringContainsString('bar', $messages['fooMessage']);
        self::assertStringContainsString('bar was passed', $messages['fooMessage']);
    }

    public function testGetMessageTemplates(): void
    {
        $messages = $this->validator->getMessageTemplates();

        self::assertSame([
            'fooMessage' => '%value% was passed',
            'barMessage' => '%value% was wrong',
        ], $messages);

        self::assertSame([
            ConcreteValidator::FOO_MESSAGE => '%value% was passed',
            ConcreteValidator::BAR_MESSAGE => '%value% was wrong',
        ], $messages);
    }

    public function testInvokeProxiesToIsValid(): void
    {
        $validator = new ConcreteValidator();

        self::assertFalse($validator('foo'));
        self::assertContains('foo was passed', $validator->getMessages());
    }

    public function testTranslatorMethods(): void
    {
        $translatorMock = $this->createMock(Translator::class);
        $this->validator->setTranslator($translatorMock, 'foo');

        self::assertSame($translatorMock, $this->validator->getTranslator());
        self::assertSame('foo', $this->validator->getTranslatorTextDomain());
        self::assertTrue($this->validator->hasTranslator());
        self::assertTrue($this->validator->isTranslatorEnabled());

        $this->validator->setTranslatorEnabled(false);

        self::assertFalse($this->validator->isTranslatorEnabled());
    }

    public function testDefaultTranslatorMethods(): void
    {
        self::assertFalse(AbstractValidator::hasDefaultTranslator());
        self::assertNull(AbstractValidator::getDefaultTranslator());
        self::assertSame('default', AbstractValidator::getDefaultTranslatorTextDomain());

        self::assertFalse($this->validator->hasTranslator());

        $translatorMock = $this->createMock(Translator::class);
        AbstractValidator::setDefaultTranslator($translatorMock, 'foo');

        self::assertSame($translatorMock, AbstractValidator::getDefaultTranslator());
        self::assertSame($translatorMock, $this->validator->getTranslator());
        self::assertSame('foo', AbstractValidator::getDefaultTranslatorTextDomain());
        self::assertSame('foo', $this->validator->getTranslatorTextDomain());
        self::assertTrue(AbstractValidator::hasDefaultTranslator());
    }

    public function testMessageCreationWithNestedArrayValueDoesNotRaiseNotice(): void
    {
        $r = new ReflectionMethod($this->validator, 'createMessage');

        $message = $r->invoke($this->validator, 'fooMessage', ['foo' => ['bar' => 'baz']]);

        self::assertStringContainsString('foo', $message);
        self::assertStringContainsString('bar', $message);
        self::assertStringContainsString('baz', $message);
    }

    public function testNonIdenticalMessagesAllReturned(): void
    {
        self::assertFalse($this->validator->isValid('invalid'));

        $messages = $this->validator->getMessages();

        self::assertCount(2, $messages);
        self::assertSame([
            ConcreteValidator::FOO_MESSAGE => 'invalid was passed',
            ConcreteValidator::BAR_MESSAGE => 'invalid was wrong',
        ], $messages);
    }

    public function testIdenticalMessagesNotReturned(): void
    {
        $this->validator->setMessage('Default error message');

        self::assertFalse($this->validator->isValid('invalid'));

        $messages = $this->validator->getMessages();

        self::assertCount(1, $messages);
        self::assertSame('Default error message', reset($messages));
    }

    public function testIdenticalAndNonIdenticalMessagesReturned(): void
    {
        $validator = new EmailAddress();

        self::assertFalse($validator->isValid('invalid@email.coma'));
        self::assertCount(3, $validator->getMessages());
        self::assertArrayHasKey(EmailAddress::INVALID_HOSTNAME, $validator->getMessages());
        self::assertArrayHasKey(Hostname::UNKNOWN_TLD, $validator->getMessages());
        self::assertArrayHasKey(Hostname::LOCAL_NAME_NOT_ALLOWED, $validator->getMessages());

        $validator->setMessages([
            EmailAddress::INVALID_HOSTNAME => 'This is the same error message',
            Hostname::UNKNOWN_TLD          => 'This is the same error message',
        ]);

        self::assertFalse($validator->isValid('invalid@email.coma'));
        self::assertCount(2, $validator->getMessages());
        self::assertArrayHasKey(EmailAddress::INVALID_HOSTNAME, $validator->getMessages());
        self::assertArrayHasKey(Hostname::LOCAL_NAME_NOT_ALLOWED, $validator->getMessages());
    }

    public function testRetrievingUnknownOptionRaisesException(): void
    {
        $option = 'foo';

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf("Invalid option '%s'", $option));

        $this->validator->getOption($option);
    }

    /**
     * @psalm-return array<string, array{scalar|object|null}>
     */
    public static function invalidOptionsArguments(): array
    {
        return [
            'null'       => [null],
            'true'       => [true],
            'false'      => [false],
            'zero'       => [0],
            'int'        => [1],
            'zero-float' => [0.0],
            'float'      => [1.1],
            'string'     => ['string'],
            'object'     => [(object) []],
        ];
    }

    /**
     * @psalm-param scalar|object|null $options
     */
    #[DataProvider('invalidOptionsArguments')]
    public function testSettingOptionsWithNonTraversableRaisesException($options): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('setOptions expects an array or Traversable');

        $this->validator->setOptions($options);
    }
}
