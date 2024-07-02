# Validation Messages

Each validator based on `Laminas\Validator\ValidatorInterface` provides one or
multiple messages in the case of a failed validation. You can use this
information to set your own messages, or to translate existing messages which a
validator could return to something different.

Validation messages are defined as constant/template pairs, with the constant
representing an immutable key. Such constants are defined per-class. Let's
look into `Laminas\Validator\Digits` for a descriptive example:

```php
protected array $messageTemplates = [
    self::NOT_DIGITS   => 'The input must contain only digits',
];
```

The constant `self::NOT_DIGITS` refers to the failure and is used as the
message key, and the message template itself is used as the value within the
message array.

You can retrieve all message templates from a validator by using the
`getMessageTemplates()` method. It returns the above array containing all
messages a validator could return in the case of a failed validation.

```php
$validator = new Laminas\Validator\Digits();
$messages  = $validator->getMessageTemplates();
```

Using the `setMessage()` method you can set another message to be returned in
case of the specified failure.

```php
use Laminas\Validator\Digits;

$validator = new Digits();
$validator->setMessage('Please enter some numbers', Digits::NOT_DIGITS);
```

The second parameter defines the failure which will be overridden. When you omit
this parameter, then the given message will be set for all possible failures of
this validator.

## Limit the size of a validation message

Sometimes it is necessary to limit the maximum size a validation message can
have; as an example, when your view allows a maximum size of 100 chars to be
rendered on one line. To enable this, `Laminas\Validator\AbstractValidator`
is able to automatically limit the maximum returned size of a validation
message.

To get the actual set size use `Laminas\Validator\AbstractValidator::getMessageLength()`.
If it is `-1`, then the returned message will not be truncated. This is default
behaviour.

To limit the returned message size, use `Laminas\Validator\AbstractValidator::setMessageLength()`.
Set it to any integer size you need. When the returned message exceeds the set
size, then the message will be truncated and the string `**...**` will be added
instead of the rest of the message.

```php
Laminas\Validator\AbstractValidator::setMessageLength(100);
```

> ### Where is this parameter used?
>
> The set message length is used for all validators, even for self defined ones,
> as long as they extend `Laminas\Validator\AbstractValidator`.
