# Iban Validator

`Laminas\Validator\Iban` validates if a given value could be a IBAN number. IBAN is
the abbreviation for "International Bank Account Number".

## Supported Options

The following options are supported for `Laminas\Validator\Iban`:

- `country_code`: Sets the country code which is used to get the IBAN format
  for validation.
- `allow_non_sepa`: A boolean that limits allowable account numbers to SEPA countries when `false`

## IBAN Validation

IBAN numbers are always related to a country. This means that different
countries use different formats for their IBAN numbers. This is the reason why
IBAN numbers always need a country code. By knowing this we already know how
to use `Laminas\Validator\Iban`.

### Ungreedy IBAN Validation

Sometimes it is useful just to validate if the given value is a IBAN number or
not. This means that you don't want to validate it against a defined country.
This can be done by omitting the `country_code` option.

```php
$validator = new Laminas\Validator\Iban();

if ($validator->isValid('AT611904300234573201')) {
    // IBAN appears to be valid
} else {
    // IBAN is not valid
}
```

In this situation, any IBAN number from any country will considered valid. Note
that this should not be done when you accept only accounts from a single
country!

### Region Aware IBAN Validation

To validate against a defined country, you must provide a country code. You can
do this during instantiation via the option `country_code`.

```php
$validator = new Laminas\Validator\Iban(['country_code' => 'AT']);

if ($validator->isValid('AT611904300234573201')) {
    // IBAN appears to be valid
} else {
    // IBAN is not valid
}
```

### Restrict to SEPA Countries

To only accept bank accounts from within the Single Euro Payments Area (SEPA), you can set the option `allow_non_sepa` to `false`:

```php
$validator = new Laminas\Validator\Iban(['allow_non_sepa' => false]);

$validator->isValid('AT611904300234573201'); // true
$validator->isValid('BA391290079401028494'); // false

```
