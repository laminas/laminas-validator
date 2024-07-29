# Isbn Validator

`Laminas\Validator\Isbn` allows you to validate ISBN-10 or ISBN-13 numbers.

## Supported Options

The following options are supported for `Laminas\Validator\Isbn`:

- `type`: Defines the allowed ISBN types. It defaults to
  `Laminas\Validator\Isbn::AUTO`. For details, take a look at the section on
  [explicit types](#setting-an-explicit-isbn-validation-type).

## Basic Usage

A basic example of usage is below:

```php
$validator = new Laminas\Validator\Isbn();

if ($validator->isValid($isbn)) {
    // isbn is valid
} else {
    // isbn is not valid
}
```

This will validate any ISBN-10 or ISBN-13 with or without separators.

## Setting an Explicit ISBN Validation Type

An example of an ISBN type restriction follows:

```php
use Laminas\Validator\Isbn;

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

## About Separators

It is common for ISBN numbers to be formatted with either spaces or dash to make the numbers easier to read.
This validator strips ` ` and `-` prior to validation.

```php
$validator = new Laminas\Validator\Isbn();

$validator->isValid('0060929871'); // true
$validator->isValid('0-06-092987-1'); // true
$validator->isValid('0 06 092987 1'); // true
```
