# Digits Validator

`Laminas\Validator\Digits` validates if a given value contains only digits.

## Supported Options

There are no additional options for `Laminas\Validator\Digits`:

## Validating Digits

To validate if a given value contains only digits and no other characters,
call the validator as shown below:

```php
$validator = new Laminas\Validator\Digits();

$validator->isValid("1234567890"); // returns true
$validator->isValid(1234);         // returns true
$validator->isValid('1a234');      // returns false
```

NOTE: **Validating Numbers**
When you want to validate numbers or numeric values, be aware that this validator only validates *digits*.
This means that any other sign like a thousand separator or a comma will not pass this validator.
In this case you should use [`Laminas\I18n\Validator\IsInt`](https://docs.laminas.dev/laminas-i18n/validators/is-int/) or [`Laminas\I18n\Validator\IsFloat`](https://docs.laminas.dev/laminas-i18n/validators/is-float/).
