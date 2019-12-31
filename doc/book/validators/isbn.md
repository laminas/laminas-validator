# Isbn Validator

`Laminas\Validator\Isbn` allows you to validate an ISBN-10 or ISBN-13 value.

## Supported options

The following options are supported for `Laminas\Validator\Isbn`:

- `separator`: Defines the allowed separator for the ISBN number. It defaults to
  an empty string.
- `type`: Defines the allowed ISBN types. It defaults to
  `Laminas\Validator\Isbn::AUTO`. For details, take a look at the section on
  [explicit types](#setting-an-explicit-isbn-validation-type).

## Basic usage

A basic example of usage is below:

```php
$validator = new Laminas\Validator\Isbn();

if ($validator->isValid($isbn)) {
    // isbn is valid
} else {
    // isbn is not valid
}
```

This will validate any ISBN-10 and ISBN-13 without separator.

## Setting an explicit ISBN validation type

An example of an ISBN type restriction follows:

```php
use Laminas\Validator\Isbn;

$validator = new Isbn();
$validator->setType(Isbn::ISBN13);

// OR
$validator = new Isbn([ 'type' => Isbn::ISBN13]);

if ($validator->isValid($isbn)) {
    // this is a valid ISBN-13 value
} else {
    // this is an invalid ISBN-13 value
}
```

The above will validate only ISBN-13 values.

Valid types include:

- `Laminas\Validator\Isbn::AUTO` (default)
- `Laminas\Validator\Isbn::ISBN10`
- `Laminas\Validator\Isbn::ISBN13`

## Specifying a separator restriction

An example of separator restriction:

```php
$validator = new Laminas\Validator\Isbn();
$validator->setSeparator('-');

// OR
$validator = new Laminas\Validator\Isbn(['separator' => '-']);

if ($validator->isValid($isbn)) {
    // this is a valid ISBN with separator
} else {
    // this is an invalid ISBN with separator
}
```

> ### Values without separators
>
> This will return `false` if `$isbn` doesn't contain a separator **or** if it's
> an invalid *ISBN* value.

Valid separators include:

- `` (empty) (default)
- `-` (hyphen)
- ` ` (space)
