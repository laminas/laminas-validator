# Callback Validator

`Laminas\Validator\Callback` allows you to provide a callback with which to
validate a given value.

## Supported options

The following options are supported for `Laminas\Validator\Callback`:

- `callback`: Sets the callback which will be called for the validation.
- `callbackOptions`: Sets the additional options which will be given to the validator
  and/or callback.
- `throwExceptions`: When true, [allows exceptions thrown inside of callbacks to propagate](#exceptions-within-callbacks).
- `bind`: When true, the callback will be bound to the scope of the validator instance

## Basic usage

The simplest use case is to pass a function as a callback. Consider the
following function:

```php
function myMethod($value)
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

## Usage with closures

The `Callback` validator supports any PHP callable, including PHP
[closures](http://php.net/functions.anonymous).

```php
$valid = new Laminas\Validator\Callback(function($value) {
    // some validation
    return true;
});

if ($valid->isValid($input)) {
    // input appears to be valid
} else {
    // input is invalid
}
```

## Usage with class-based callbacks

Of course it's also possible to use a class method as callback. Consider the
following class definition:

```php
class MyClass
{
    public function myMethod($value)
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
    public static function test($value)
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
    public function __invoke($value)
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

## Adding options

`Laminas\Validator\Callback` also allows the usage of options which are provided as
additional arguments to the callback.

Consider the following class and method definition:

```php
class MyClass
{
    public static function myMethod($value, $option)
    {
        // some validation
        return true;
    }

    /**
     * Or, to use with contextual validation
     */
    public static function myMethod($value, $context, $option)
    {
        // some validation
        return true;
    }

}
```

There are two ways to inform the validator of additional options: pass them in
the constructor, or pass them to the `setOptions()` method.

To pass them to the constructor, you would need to pass an array containing two
keys, `callback` and `callbackOptions`:

```php
$valid = new Laminas\Validator\Callback([
    'callback'        => [MyClass::class, 'myMethod'],
    'callbackOptions' => $options,
]);

if ($valid->isValid($input)) {
    // input appears to be valid
} else {
    // input is invalid
}
```

Otherwise, you may pass them to the validator after instantiation:

```php
$valid = new Laminas\Validator\Callback([MyClass::class, 'myMethod']);
$valid->setOptions($options);

if ($valid->isValid($input)) {
    // input appears to be valid
} else {
    // input is invalid
}
```

When there are additional values given to `isValid()`, then these values will be
passed as an additional argument:

```php
$valid = new Laminas\Validator\Callback([MyClass::class, 'myMethod']);
$valid->setOptions($options);

if ($valid->isValid($input, $context)) {
    // input appears to be valid
} else {
    // input is invalid
}
```

When making the call to the callback, the value to be validated will always be
passed as the first argument to the callback followed by all other values given
to `isValid()`; all other options will follow it. The amount and type of options
which can be used is not limited.

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

Binding only occurs when the `callback` option and the `bind` option are used in the constructor via an options array as shown above.

## Exceptions within Callbacks

By default, the callback validator will catch any `Exception` thrown inside the callback and return false.
The error message will indicate callback failure as opposed to invalid input.

There is a third option `throwExceptions` that when `true` will re-throw exceptions that occur inside the callback.

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
