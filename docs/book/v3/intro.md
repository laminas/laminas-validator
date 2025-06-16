# Introduction

laminas-validator provides a set of commonly needed validators. It also provides a
simple validator chaining mechanism by which multiple validators may be applied
to a single datum in a user-defined order.

## What Is a Validator?

A validator examines its input with respect to some requirements and produces a
boolean result indicating whether the input successfully validates against the
requirements. If the input does not meet the requirements, a validator may
additionally provide information about which requirement(s) the input does not
meet.

For example, a web application might require that a username be between six and
twelve characters in length, and may only contain alphanumeric characters. A
validator can be used for ensuring that a username meets these requirements. If
a chosen username does not meet one or both of the requirements, it would be
useful to know which of the requirements the username fails to meet.

## Basic Usage of Validators

Having defined validation in this way provides the foundation for
`Laminas\Validator\ValidatorInterface`, which defines two methods, `isValid()` and
`getMessages()`. The `isValid()` method performs validation upon the provided
value, returning `true` if and only if the value passes against the validation
criteria.

If `isValid()` returns `false`, the `getMessages()` method will return an array
of messages explaining the reason(s) for validation failure. The array keys are
short strings that identify the reasons for validation failure, and the array
values are the corresponding human-readable string messages. The keys and values
are class-dependent; each validation class defines its own set of validation
failure messages and the unique keys that identify them. Each class also has a
`const` definition that matches each identifier for a validation failure cause.

> ### Stateful Validators
>
> The `getMessages()` methods return validation failure information only for the
> most recent `isValid()` call. Each call to `isValid()` clears any messages and
> errors caused by a previous `isValid()` call, because it's likely that each
> call to `isValid()` is made for a different input value.

The following example illustrates validation of an e-mail address:

```php
use Laminas\Validator\EmailAddress;

$validator = new EmailAddress();

if ($validator->isValid($email)) {
    // email appears to be valid
} else {
    // email is invalid; print the reasons
    foreach ($validator->getMessages() as $messageId => $message) {
        printf("Validation failure '%s': %s\n", $messageId, $message);
    }
}
```

## Customizing Messages

Validator classes provide a `setMessage()` method with which you can specify the
format of a message returned by `getMessages()` in case of validation failure.
The first argument of this method is a string containing the error message. You
can include tokens in this string which will be substituted with data relevant
to the validator. The token `%value%` is supported by all validators; this is
substituted with the value you passed to `isValid()`. Other tokens may be
supported on a case-by-case basis in each validation class. For example, `%max%`
is a token supported by `Laminas\Validator\LessThan`. The `getMessageVariables()`
method returns an array of variable tokens supported by the validator.

The second optional argument is a string that identifies the validation failure
message template to be set, which is useful when a validation class defines more
than one cause for failure. If you omit the second argument, `setMessage()`
assumes the message you specify should be used for the first message template
declared in the validation class. Many validation classes only have one error
message template defined, so there is no need to specify which message template
you are changing.

```php
use Laminas\Validator\StringLength;

$validator = new StringLength(8);

$validator->setMessage(
    'The string \'%value%\' is too short; it must be at least %min% characters',
    StringLength::TOO_SHORT
);

if (! $validator->isValid('word')) {
    $messages = $validator->getMessages();
    echo current($messages);

    // "The string 'word' is too short; it must be at least 8 characters"
}
```

You can set multiple messages using the `setMessages()` method. Its argument is
an array containing key/message pairs.

```php
use Laminas\Validator\StringLength;

$validator = new StringLength(['min' => 8, 'max' => 12]);

$validator->setMessages([
    StringLength::TOO_SHORT => 'The string \'%value%\' is too short',
    StringLength::TOO_LONG  => 'The string \'%value%\' is too long',
]);
```

If your application requires even greater flexibility with which it reports
validation failures, you can access properties by the same name as the message
tokens supported by a given validation class. The `value` property is always
available in a validator; it is the value you specified as the argument of
`isValid()`. Other properties may be supported on a case-by-case basis in each
validation class.

```php
use Laminas\Validator\StringLength;

$validator = new StringLength(['min' => 8, 'max' => 12]);

if (! $validator->isValid('word')) {
    printf(
        "Word failed: %s; its length is not between %d and %d\n",
        $validator->value,
        $validator->min,
        $validator->max
    );
}
```

## Translating Messages

> ### Installation Requirements
>
> The translation of validator messages depends on the laminas-i18n component, so
> be sure to have it installed before getting started:
>
> ```bash
> $ composer require laminas/laminas-i18n
> ```

Validator classes provide a `setTranslator()` method with which you can specify an instance of `Laminas\Translator\TranslatorInterface` which will translate the messages in case of a validation failure.

The `getTranslator()` method returns the translator instance.

```php
use Laminas\I18n\Translator\Translator;
use Laminas\Validator\StringLength;

$validator = new StringLength(['min' => 8, 'max' => 12]);
$translator = new Translator();
// configure the translator...

$validator->setTranslator($translator);
```

Avoid using the `setTranslator` method if possible and prefer to set an instance-specific translator using options in the following way:

```php
use Laminas\I18n\Translator\Translator;
use Laminas\Validator\StringLength;

$translator = new Translator();

$validator = new StringLength([
    'min' => 8,
    'max' => 12,
    'translator' => $translator,
]);
```

### Setting a "Global" Translator Using Static Methods

It is possible to set a `static` translator for any translator that inherits from `AbstractTranslator` using the `AbstractValidator::setDefaultTranslator()` method.

Once set, this 'static' translator can also be retrieved from any instance with the `getTranslator()` method.

```php
use Laminas\I18n\Translator\Translator;
use Laminas\Validator\AbstractValidator;

$translator = new Translator();
// configure the translator...

AbstractValidator::setDefaultTranslator($translate);
```

Setting the translator with this static method is not best practice, though it will continue to work for the 3.0 series of releases.

Ideally, your default translator will be provided to validator instances via the `ValidatorPluginManager`.

### Enabling a "Global" Translator Using the Validator Plugin Manager

Assuming you are using `laminas/laminas-servicemanager` for dependency injection, all that is required for your translator to be made available to any validator instance retrieved from the validator plugin manager is to alias `Laminas\Translator\TranslatorInterface` to your translator instance.

```php
// config/autoload/dependencies.global.php

return [
    'dependencies' => [
        'factories' => [
            \Laminas\Translator\TranslatorInterface::class => \My\Translator\Factory::class,
        ],
    ],
];
```

Providing a translator can be retrieved from the service manager, it will be injected into each validator that is retrieved from the validator plugin manager.

### Disable Translation Per Validator Instance

Sometimes it is necessary to disable the translator within a validator. To achieve this you can set the `translatorEnabled` option for any validator that inherits from `AbstractValidator` to `false`:

```php
use Laminas\Validator\StringLength;

$validator = new StringLength([
    'min' => 8,
    'max' => 12,
    'translatorEnabled' => false,
]);
```
