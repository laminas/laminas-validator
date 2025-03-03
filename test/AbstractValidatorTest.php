<?php

declare(strict_types=1);

namespace LaminasTest\Validator;

use Laminas\Validator\AbstractValidator;
use Laminas\Validator\EmailAddress;
use Laminas\Validator\Hostname;
use LaminasTest\Validator\TestAsset\ConcreteValidator;
use LaminasTest\Validator\TestAsset\Translator;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use stdClass;

use function reset;
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
        $translator = new Translator([]);
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
        $translator = new Translator([
            '%value% was passed' => 'This is the translated message for %value%',
        ]);
        $this->validator->setTranslator($translator);

        self::assertFalse($this->validator->isValid('bar'));

        $messages = $this->validator->getMessages();

        self::assertArrayHasKey('fooMessage', $messages);
        self::assertStringContainsString('bar', $messages['fooMessage'], var_export($messages, true));
        self::assertStringContainsString('This is the translated message for bar', $messages['fooMessage']);
    }

    public function testValueIsObfuscatedWheObscureValueFlagIsTrue(): void
    {
        $validator = new ConcreteValidator([
            'valueObscured' => true,
        ]);

        self::assertFalse($validator->isValid('foobar'));

        $messages = $validator->getMessages();

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

    public function testCanDisableTranslator(): void
    {
        $translator = new Translator([
            '%value% was passed' => 'This is the translated message for %value%',
        ]);

        $validator = new ConcreteValidator([
            'translator' => $translator,
        ]);

        self::assertFalse($validator->isValid('bar'));

        $messages = $validator->getMessages();

        self::assertArrayHasKey('fooMessage', $messages);
        self::assertStringContainsString('bar', $messages['fooMessage']);
        self::assertStringContainsString('This is the translated message for bar', $messages['fooMessage']);

        $validator = new ConcreteValidator([
            'translator'        => $translator,
            'translatorEnabled' => false,
        ]);

        self::assertFalse($validator->isValid('bar'));

        $messages = $validator->getMessages();

        self::assertArrayHasKey('fooMessage', $messages);
        self::assertStringContainsString('bar', $messages['fooMessage']);
        self::assertStringContainsString('bar was passed', $messages['fooMessage']);
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
    }

    public function testDefaultTranslatorMethods(): void
    {
        $validator = new ConcreteValidator();

        self::assertNull($validator->getTranslator());

        $translator = new Translator([]);
        AbstractValidator::setDefaultTranslator($translator, 'foo');

        self::assertNull($validator->getTranslator());

        $validator2 = new ConcreteValidator();
        self::assertSame($translator, $validator2->getTranslator());
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

        $validator = new EmailAddress([
            'messages' => [
                EmailAddress::INVALID_HOSTNAME => 'This is the same error message',
                Hostname::UNKNOWN_TLD          => 'This is the same error message',
            ],
        ]);

        self::assertFalse($validator->isValid('invalid@email.coma'));
        self::assertCount(2, $validator->getMessages());
        self::assertArrayHasKey(EmailAddress::INVALID_HOSTNAME, $validator->getMessages());
        self::assertArrayHasKey(Hostname::LOCAL_NAME_NOT_ALLOWED, $validator->getMessages());
    }

    public function testThatRepeatedValidationsClearErrorMessages(): void
    {
        $validator = new ConcreteValidator();

        self::assertFalse($validator->isValid('Foo'));
        self::assertArrayHasKey(ConcreteValidator::BAR_MESSAGE, $validator->getMessages());

        self::assertTrue($validator->isValid($validator->validValue));

        self::assertSame([], $validator->getMessages());
    }
}
