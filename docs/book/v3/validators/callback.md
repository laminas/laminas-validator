# Callback Validator

`Laminas\Validator\Callback` allows you to provide a callback with which to
validate a given value.

## Supported Options

The following options are supported for `Laminas\Validator\Callback`:

- `callback`: Sets the callback which will be called for the validation.
- `callbackOptions`: Sets the additional arguments that will be given to the validator callback.
- `throwExceptions`: When true, [allows exceptions thrown inside of callbacks to propagate](#exceptions-within-callbacks).
- `bind`: When true, the callback will bound to the validator's scope allowing the closure to call internal methods of the validator.

## Basic Usage

The simplest use case is to pass a function as a callback. Consider the
following function:

```php
function myMethod(mixed $value): bool
{
    // some validation
    return true;
}
```

To use it within `Laminas\Validator\Callback`, pass it to the constructor

```php
$valid = new Laminas\Validator\Callback('myMethod');
if ($valid->isValid($input)) {
    // input appears to be valid
} else {
    // input is invalid
}
```

## Usage with Closures

The `Callback` validator supports any PHP callable, including PHP
[closures](http://php.net/functions.anonymous).

```php
$valid = new Laminas\Validator\Callback(function(mixed $value): bool {
    // some validation
    return true;
});

if ($valid->isValid($input)) {
    // input appears to be valid
} else {
    // input is invalid
}
```

## Usage with Class-Based Callbacks

Of course, it's also possible to use a class method as callback. Consider the
following class definition:

```php
class MyClass
{
    public function myMethod(mixed $value): bool
    {
        // some validation
        return true;
    }
}
```

To use it with the `Callback` validator, pass a callable using an instance of
the class:

```php
$valid = new Laminas\Validator\Callback([new MyClass, 'myMethod']);
if ($valid->isValid($input)) {
    // input appears to be valid
} else {
    // input is invalid
}
```

You may also define a static method as a callback. Consider the following class
definition and validator usage:

```php
class MyClass
{
    public static function test(mixed $value): bool
    {
        // some validation
        return true;
    }
}

$valid = new Laminas\Validator\Callback([MyClass::class, 'test']);
if ($valid->isValid($input)) {
    // input appears to be valid
} else {
    // input is invalid
}
```

Finally, you may define the magic method `__invoke()` in your class. If you do
so, you can provide a class instance itself as the callback:

```php
class MyClass
{
    public function __invoke(mixed $value): bool
    {
        // some validation
        return true;
    }
}

$valid = new Laminas\Validator\Callback(new MyClass());
if ($valid->isValid($input)) {
    // input appears to be valid
} else {
    // input is invalid
}
```

## Validation Context Argument

Your callback will also receive the validation context, if it is available, as an associative array that typically represents the entire, un-filtered and un-validated payload, i.e. `$_POST`.

The context will always be present as a non-empty array when the validator is used via [`laminas-inputfilter`](https://docs.laminas.dev/laminas-inputfilter/) or [`laminas-form`](https://docs.laminas.dev/laminas-form/), but in standalone usage, you will need to provide it to the validator yourself:

```php
use Laminas\Validator\Callback;

$formPayload = [
    'muppet-1' => 'Kermit',
    'muppet-2' => 'Miss Piggy',
];

$validator = new Callback([
    'callback' => static function (mixed $value, array $context = []): bool {
        if ($value === 'Kermit' && $context['muppet-2'] === 'Miss Piggy') {
            return true;
        }
        
        return false;
    },
]);

$validator->isValid($formPayload['muppet-1'], $formPayload); // true
```

## Adding User-Defined Callback Arguments

`Laminas\Validator\Callback` also has a `callbackOptions` option that allows you to provide an array of additional arguments to pass to your callback after the value and the validation context.

For example:

```php
use Laminas\Validator\Callback;

$validator = new Callback([
    'callback' => static function (mixed $value, array $context = [], MuppetService $service): bool {
        if ($service->isKnownMuppet($value) && $service->isKnownMuppet($context['muppet-2'] ?? null)) {
            return true;
        }
        
        return false;
    },
    'callbackOptions' => [
        'service' => new MuppetService(),    
    ],
]);

$validator->isValid('Fozzie Bear', ['muppet-2' => 'Scooter']);
```

There is no limit to the number of arguments you can provide to your callback.

## Callbacks and Scope

By default, callbacks are executed in their own scope and do not have access to the validator instance they are executed in.

It is possible to bind your callback to the validator scope by setting the `bind` option to true.

This is useful when you wish to provide more detailed error messages in case there are multiple potential reasons for validation failure:

```php
$validator = new Laminas\Validator\Callback([
    'callback' => function (mixed $value): bool {
        if ($value === 42) {
            $this->setMessage(
                'Sorry, the meaning of life is not acceptable',
                 Laminas\Validator\Callback::INVALID_VALUE,
            );
            
            return false;
        }
        
        if ($value === 'goats') {
            $this->setMessage(
                'Sorry, I don’t like goats…',
                 Laminas\Validator\Callback::INVALID_VALUE,
            );
            
            return false;
        }
        
        return true;
    },
    'bind' => true,
]);
```

## Exceptions within Callbacks

By default, the callback validator will catch any `Exception` thrown inside the callback and return false.
The error message will indicate callback failure as opposed to invalid input.

The option `throwExceptions`, when `true`, will re-throw exceptions that occur inside the callback.

This is primarily useful in a development environment when you are testing callbacks and need to catch and verify exceptions thrown by your own application.

For example:

```php
$callback = static function (mixed $value): bool {
    if ($value === true) {
        return true;
    }
    
    throw new ApplicationException('Bad news');
}

$validator = new Laminas\Validator\Callback([
    'callback' => $callback,
    'throwExceptions' => true,
]);

$validator->isValid('Nope'); // An exception is thrown
```
