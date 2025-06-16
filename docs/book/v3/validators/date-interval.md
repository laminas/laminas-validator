# DateIntervalString Validator

`Laminas\Validator\DateIntervalString` validates if a given value is a valid `\DateInterval` specification such as `"P1DT6H"`.

## Supported Options

There are no additional options for `Laminas\Validator\DateIntervalString`:

## Validating Input

To validate if a given value is a valid specification for a date interval:

```php
$validator = new Laminas\Validator\DateIntervalString();

$validator->isValid("P1DT6H"); // returns true
$validator->isValid(1234);     // returns false
$validator->isValid('foo');    // returns false
```
