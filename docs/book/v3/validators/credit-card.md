# CreditCard Validator

`Laminas\Validator\CreditCard` allows you to validate if a given value could be a
credit card number.

A credit card contains several items of metadata, including a hologram, account
number, logo, expiration date, security code, and the card holder name. The
algorithms for verifying the combination of metadata are only known to the
issuing company, and should be verified with them for purposes of payment.
However, it's often useful to know whether a given number actually falls
within the ranges of possible numbers **prior** to performing such verification,
and, as such, `Laminas\Validator\CreditCard` verifies that the credit card number
provided is well-formed.

For those cases where you have a service that can perform comprehensive
verification, `Laminas\Validator\CreditCard` also provides the ability to attach a
service callback to trigger once the credit card number has been deemed valid;
this callback will then be triggered, and its return value will determine
overall validity.

The following issuing institutes are accepted:

- American Express
- China UnionPay
- Diners Club Card Blanche
- Diners Club International
- Diners Club US and Canada
- Discover Card
- JCB
- Laser
- Maestro
- MasterCard
- Solo
- Visa
- Visa Electron
- Russia Mir

<!-- markdownlint-disable-next-line MD001 -->
> ### Invalid Institutes
>
> The institutes **Bankcard** and **Diners Club enRoute** no longer exist, and
> are treated as invalid.
>
> **Switch** has been rebranded to **Visa** and is therefore also treated as
> invalid.

## Supported Options

The following options are supported for `Laminas\Validator\CreditCard`:

- `service`: A callback to an online service which will additionally be used for
  the validation.
- `type`: The type of credit card which will be validated. See the below list of
  institutes for details.

## Basic Usage

There are several credit card institutes which can be validated by
`Laminas\Validator\CreditCard`. Per default, all known institutes will be accepted.
See the following example:

```php
$valid = new Laminas\Validator\CreditCard();
if ($valid->isValid($input)) {
    // input appears to be valid
} else {
    // input is invalid
}
```

The above example would validate against all known credit card institutes.

## Accepting Only Specific Credit Cards

Sometimes it is necessary to accept only specific credit card institutes instead
of all; e.g., when you have a webshop which accepts only Visa and American
Express cards. `Laminas\Validator\CreditCard` allows you to do exactly this by
limiting it to exactly these institutes.

To use a limitation you can provide specific institutes at initiation.

You can provide a single institute:

```php
use Laminas\Validator\CreditCard;

$valid = new CreditCard(['type' => CreditCard::AMERICAN_EXPRESS]);
```

When you want to allow multiple institutes, then you can provide them as array:

```php
use Laminas\Validator\CreditCard;

$valid = new CreditCard([
    'type' => [
        CreditCard::AMERICAN_EXPRESS,
        CreditCard::VISA
    ],
]);
```

> ### Default Institute
>
> When no institute is given at initiation then `ALL` will be used, which sets
> all institutes at once.

## Validation Using APIs

As said before `Laminas\Validator\CreditCard` will only validate the credit card
number. Fortunately, some institutes provide online APIs which can validate a
credit card number by using algorithms which are not available to the public.
Most of these services are paid services. Therefore, this check is deactivated
per default.

When you have access to such an API, then you can use it as an add on for
`Laminas\Validator\CreditCard` and increase the security of the validation.

To do so, provide a callback in the `service` option to invoke when generic validation has passed.
This prevents the API from being called for invalid numbers, which increases the
performance of the application.

```php
use Laminas\Validator\CreditCard;

// Your service class
class CcService
{
    public function checkOnline(string $cardNumber, array $types): bool
    {
        // some online validation
    }
}

// The validation
$service = new CcService();
$valid   = new CreditCard([
    'type' => CreditCard::VISA,
    'service' => [$service, 'checkOnline'],
]);
```

The callback method will be called with the credit card number as the first
parameter, and the accepted types as the second parameter.
