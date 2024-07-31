# Explode Validator

`Laminas\Validator\Explode` executes a validator for each item exploded from an
array.

## Supported Options

The following options are supported for `Laminas\Validator\Explode`:

- `valueDelimiter`: Defines the delimiter used to explode values from an array.
  It defaults to `,`.
- `validator`: Sets the validator that will be executed on each exploded item.
  This may be a validator instance, a validator service name, or a "specification" array.
- `validatorPluginManager`: The validator plugin manager in use in your application. If this plugin manager is not provided, a plugin manager will be created with the default configuration.

## Basic Usage

To validate if every item in an array is in a specified haystack:

```php
$inArrayValidator = new Laminas\Validator\InArray([
    'haystack' => [1, 2, 3, 4, 5, 6],
]);

$explodeValidator = new Laminas\Validator\Explode([
    'validator' => $inArrayValidator
]);

$explodeValidator->isValid('1,4,6');    // returns true
$explodeValidator->isValid('1,4,6,8'); // returns false
```

## Configuration Using a Validator Specification

Instead of creating a validator instance, you can provide an array to describe the validator you wish to use for each element:

```php
$explodeValidator = new Laminas\Validator\Explode([
    'validator' => [
        'name' => Laminas\Validator\InArray::class,
        'options' => [
            'haystack' => ['a', 'b', 'c']
        ],
    ],
    'valueDelimiter' => ';',
]);

$explodeValidator->isValid('a;b'); // returns true
$explodeValidator->isValid('x;y;z');  // returns false
```
