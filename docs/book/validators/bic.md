# BIC Validator

`Laminas\Validator\BusinessIdentifierCode` validates if a given value **could be a ["Business Identifier Code" (BIC)](https://www.swift.com/standards/data-standards/bic)** as defined by [ISO 9362](https://wikipedia.org/wiki/ISO_9362).
A BIC is a unique identification code for financial and non-financial institutions.

## Supported options

There are no additional supported options for the `BusinessIdentifierCode` validator.

## BIC validation

BICs should be a string which length should be equal to 8 or 11.

- The 4 first characters can only be letters and it is used to identify a bank or an institution.

- The following 2 characters can only be letters too and it should be a country code assigned within ISO 3166-1 alpha-2.
  The only exception is the code 'XK' used for the Republic of Kosovo.

- The following 2 characters can be letters or digits. It is used to represent a location (like a city)

- The last 3 characters are optional and can be letters or digits, generally to represent a branch office.
  The code 'XXX' is often used to represent the AIN office when the 11 character code is used.

## Basic usage

```php
$validator = new Laminas\Validator\BusinessIdentifierCode();

if ($validator->isValid('DEUTDEFF')) {
    // bic appears to be valid
} else {
    // bic is invalid; print the reasons
}
```
