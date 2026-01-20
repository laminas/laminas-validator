# KeyExists Validator

This validator allows you to validate if a given key is present in an iterable.

## Supported Options

The following options are supported for `Laminas\Validator\KeyExists`:

- `key`: Sets the key to be checked. `string` and `int` are supported.

## Basic Usage

```php
$validator = new Laminas\Validator\KeyExists([
    'key' => 'muppet',
]);

$validInputs = [
    ['muppet' => 'Kermit'],
    new ArrayObject(['muppet' => 'Miss Piggy']),
];

foreach ($validInputs as $input) {
    $result = $validator->isValid($input); // true
}

// Non-exhaustive examples of invalid inputs:
$invalidInputs = [
    'not an array',
    null,
    1,
    'muppet',
];

foreach ($invalidInputs as $input) {
    $result = $validator->isValid($input); // false
}
```

### Integer Keys

PHP casts numeric strings to integers when they are used as array keys:

```php
$array = ['1' => 'Foo'];
// Becomes [1 => 'Foo'];
```

This validator does the same thing for integer keys:

```php
$validator = new Laminas\Validator\KeyExists([
    'key' => '2',
]);

$validator->isValid(['a', 'b', 'c']); // true
```

Using a floating point number as a key is not supported because PHP casts floats to integers _(And will issue a deprecation on all supported versions of PHP)_.
However, using a string decimal _is_ supported:

```php
$validator = new Laminas\Validator\KeyExists([
    'key' => '1.42',
]);

$validator->isValid(['1.0' => 'a', '1.42' => 'b']); // true
```

## Additional Notes

Internally the validator calls [`iterator_to_array`](https://www.php.net/iterator_to_array) on the input to be validated.
This may not be desirable if, for example, you are validating a queue where each item is dequeued during iteration, or there are other side effects of iteration on the subject.
