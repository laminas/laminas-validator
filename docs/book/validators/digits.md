# Digits Validator

`Laminas\Validator\Digits` validates if a given value contains only digits.

> ### Installation requirements
>
> `Laminas\Validator\Digits` depends on the laminas-filter component, so be sure to
> have it installed before getting started:
>
> ```bash
> $ composer require laminas/laminas-filter
> ```

## Supported options

There are no additional options for `Laminas\Validator\Digits`:

## Validating digits

To validate if a given value contains only digits and no other characters,
call the validator as shown below:

```php
$validator = new Laminas\Validator\Digits();

$validator->isValid("1234567890"); // returns true
$validator->isValid(1234);         // returns true
$validator->isValid('1a234');      // returns false
```

> ### Validating numbers
>
> When you want to validate numbers or numeric values, be aware that this
> validator only validates *digits*. This means that any other sign like a
> thousand separator or a comma will not pass this validator. In this case you
> should use `Laminas\I18n\Validator\IsInt` or `Laminas\I18n\Validator\IsFloat`.
