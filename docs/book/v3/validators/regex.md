# Regex Validator

This validator allows you to validate if a given string conforms a defined regular expression.

It will *not* automatically cast integers or floats to string prior to evaluation - when provided a non string value, validation will fail.

## Supported options

The following options are supported for `Laminas\Validator\Regex`:

- `pattern`: Sets the regular expression pattern for this validator.

## Usage

Validation with regular expressions allows complex validations without writing a custom validator.

```php
$validator = new Laminas\Validator\Regex(['pattern' => '/^Test/']);

$validator->isValid("Test"); // returns true
$validator->isValid("Testing"); // returns true
$validator->isValid("Pest"); // returns false
```

The pattern uses the same syntax as `preg_match()`. For details about regular
expressions take a look into [PHP's manual about PCRE pattern
syntax](http://php.net/reference.pcre.pattern.syntax).
