<?php

declare(strict_types=1);

namespace LaminasTest\Validator;

use Laminas\Validator\Exception\InvalidArgumentException;
use Laminas\Validator\StringLength;
use PHPUnit\Framework\TestCase;

use function array_keys;
use function current;

/**
 * @group Laminas_Validator
 */
final class MessageTest extends TestCase
{
    private StringLength $validator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = new StringLength(4, 8);
    }

    /**
     * Ensures that we can change a specified message template by its key
     * and that this message is returned when the input is invalid.
     */
    public function testSetMessage(): void
    {
        $inputInvalid = 'abcdefghij';

        self::assertFalse($this->validator->isValid($inputInvalid));

        $messages = $this->validator->getMessages();

        self::assertSame('The input is more than 8 characters long', current($messages));

        $this->validator->setMessage(
            'Your value is too long',
            StringLength::TOO_LONG
        );

        self::assertFalse($this->validator->isValid('abcdefghij'));

        $messages = $this->validator->getMessages();

        self::assertSame('Your value is too long', current($messages));
    }

    /**
     * Ensures that if we don't specify the message key, it uses
     * the first one in the list of message templates.
     * In the case of Laminas_Validate_StringLength, TOO_SHORT is
     * the one we should expect to change.
     */
    public function testSetMessageDefaultKey(): void
    {
        $this->validator->setMessage(
            'Your value is too short',
            StringLength::TOO_SHORT
        );

        self::assertFalse($this->validator->isValid('abc'));

        $messages = $this->validator->getMessages();

        self::assertSame('Your value is too short', current($messages));

        $errors = array_keys($this->validator->getMessages());

        self::assertSame(StringLength::TOO_SHORT, current($errors));
    }

    /**
     * Ensures that we can include the %value% parameter in the message,
     * and that it is substituted with the value we are validating.
     */
    public function testSetMessageWithValueParam(): void
    {
        $this->validator->setMessage(
            "Your value '%value%' is too long",
            StringLength::TOO_LONG
        );

        $inputInvalid = 'abcdefghij';

        self::assertFalse($this->validator->isValid($inputInvalid));

        $messages = $this->validator->getMessages();

        self::assertSame("Your value '$inputInvalid' is too long", current($messages));
    }

    /**
     * Ensures that we can include the %length% parameter in the message,
     * and that it is substituted with the length of the value we are validating.
     */
    public function testSetMessageWithLengthParam(): void
    {
        $this->validator->setMessage(
            "The length of your value is '%length%'",
            StringLength::TOO_LONG
        );
        $inputInvalid = 'abcdefghij';

        self::assertFalse($this->validator->isValid($inputInvalid));

        $messages = $this->validator->getMessages();

        self::assertSame("The length of your value is '10'", current($messages));
    }

    /**
     * Ensures that we can include another parameter, defined on a
     * class-by-class basis, in the message string.
     * In the case of Laminas_Validate_StringLength, one such parameter
     * is %max%.
     */
    public function testSetMessageWithOtherParam(): void
    {
        $this->validator->setMessage(
            'Your value is too long, it should be no longer than %max%',
            StringLength::TOO_LONG
        );

        $inputInvalid = 'abcdefghij';

        self::assertFalse($this->validator->isValid($inputInvalid));

        $messages = $this->validator->getMessages();

        self::assertSame('Your value is too long, it should be no longer than 8', current($messages));
    }

    /**
     * Ensures that if we set a parameter in the message that is not
     * known to the validator class, it is not changed; %shazam% is
     * left as literal text in the message.
     */
    public function testSetMessageWithUnknownParam(): void
    {
        $this->validator->setMessage(
            'Your value is too long, and btw, %shazam%!',
            StringLength::TOO_LONG
        );

        $inputInvalid = 'abcdefghij';

        self::assertFalse($this->validator->isValid($inputInvalid));

        $messages = $this->validator->getMessages();

        self::assertSame('Your value is too long, and btw, %shazam%!', current($messages));
    }

    /**
     * Ensures that the validator throws an exception when we
     * try to set a message for a key that is unknown to the class.
     */
    public function testSetMessageExceptionInvalidKey(): void
    {
        $keyInvalid = 'invalidKey';

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('No message template exists for key');

        $this->validator->setMessage(
            'Your value is too long',
            $keyInvalid
        );
    }

    /**
     * Ensures that we can set more than one message at a time,
     * by passing an array of key/message pairs.  Both messages
     * should be defined.
     */
    public function testSetMessages(): void
    {
        $this->validator->setMessages(
            [
                StringLength::TOO_LONG  => 'Your value is too long',
                StringLength::TOO_SHORT => 'Your value is too short',
            ]
        );

        self::assertFalse($this->validator->isValid('abcdefghij'));

        $messages = $this->validator->getMessages();

        self::assertSame('Your value is too long', current($messages));

        self::assertFalse($this->validator->isValid('abc'));

        $messages = $this->validator->getMessages();

        self::assertSame('Your value is too short', current($messages));
    }

    /**
     * Ensures that the magic getter gives us access to properties
     * that are permitted to be substituted in the message string.
     * The access is by the parameter name, not by the protected
     * property variable name.
     */
    public function testGetProperty(): void
    {
        $this->validator->setMessage(
            'Your value is too long',
            StringLength::TOO_LONG
        );

        $inputInvalid = 'abcdefghij';

        self::assertFalse($this->validator->isValid($inputInvalid));

        $messages = $this->validator->getMessages();

        self::assertSame('Your value is too long', current($messages));

        self::assertSame($inputInvalid, $this->validator->value);
        self::assertSame(8, $this->validator->max);
        self::assertSame(4, $this->validator->min);
    }

    /**
     * Ensures that the class throws an exception when we try to
     * access a property that doesn't exist as a parameter.
     */
    public function testGetPropertyException(): void
    {
        $this->validator->setMessage(
            'Your value is too long',
            StringLength::TOO_LONG
        );

        self::assertFalse($this->validator->isValid('abcdefghij'));

        $messages = $this->validator->getMessages();

        self::assertSame('Your value is too long', current($messages));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('No property exists by the name ');

        $this->validator->unknownProperty;
    }

    /**
     * Ensures that getMessageVariables() returns an array of
     * strings and that these strings that can be used as variables
     * in a message.
     */
    public function testGetMessageVariables(): void
    {
        $vars = $this->validator->getMessageVariables();

        self::assertIsArray($vars);
        self::assertSame(['min', 'max', 'length'], $vars);

        $message = 'variables: %notvar% ';
        foreach ($vars as $var) {
            $message .= "%$var% ";
        }

        $this->validator->setMessage($message, StringLength::TOO_SHORT);

        self::assertFalse($this->validator->isValid('abc'));

        $messages = $this->validator->getMessages();

        self::assertSame('variables: %notvar% 4 8 3 ', current($messages));
    }

    public function testEqualsMessageTemplates(): void
    {
        self::assertSame(
            [
                StringLength::INVALID,
                StringLength::TOO_SHORT,
                StringLength::TOO_LONG,
            ],
            array_keys($this->validator->getMessageTemplates())
        );
        self::assertSame($this->validator->getOption('messageTemplates'), $this->validator->getMessageTemplates());
    }

    public function testEqualsMessageVariables(): void
    {
        $messageVariables = [
            'min'    => ['options' => 'min'],
            'max'    => ['options' => 'max'],
            'length' => ['options' => 'length'],
        ];

        self::assertSame($messageVariables, $this->validator->getOption('messageVariables'));
        self::assertSame(array_keys($messageVariables), $this->validator->getMessageVariables());
    }
}
