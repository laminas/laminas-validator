# Migration from Version 2 to 3

## Changed Behaviour & Signature Changes

### Final Classes

In order to reduce maintenance burden and to help users to favour [composition over inheritance](https://en.wikipedia.org/wiki/Composition_over_inheritance), all classes, where possible have been marked as final.

### Validator Plugin Manager

#### Removal of legacy Zend aliases

All aliases that referenced the equivalent, legacy "Zend" validators have been  removed. This means that an exception will be thrown if you attempt to retrieve a validator using one of these aliases such as `Zend\Validator\NotEmpty::class`.

You will need to either update your codebase to use known aliases such as `Laminas\Validator\NotEmpty::class`, or re-implement the aliases in your configuration.

#### Removal of Service Manager v2 canonical FQCNs

There are a number of aliases left over from early versions of Service Manager where each validator would be aliased by a lowercase, normalized string such as `laminasvalidatoremail` to represent `Laminas\Validator\Email::class`. All of these aliases have been removed.

#### Removal of Laminas\i18n aliases and factories

The [`laminas-i18n`](https://docs.laminas.dev/laminas-i18n/validators/introduction/) component ships a number of validators that historically have been pre-configured from within this component. These aliases and factory entries have been removed.

[Removal of the aliases](https://github.com/laminas/laminas-validator/commit/5bbfe8baeba48f3b77c909a8d6aa930c1d2897b7) here is unlikely to cause any issues, providing you have enabled the `ConfigProvider` or `Module` from `laminas-i18n` in your application. 

## Changes to Individual Validators

### Digits

This validator no longer uses the Digits filter from `laminas/laminas-filter`, so its static filter property has been removed. This change is unlikely to cause any problems unless for some reason you have extended this class.

## Removed Features

### `Laminas\Csrf` Validator Removal

This validator was the only shipped validator with a hard dependency on the [`laminas-session`](https://docs.laminas.dev/laminas-session/) component. It has now been removed from this component and re-implemented, in a different namespace, but with the same functionality in `laminas-session`.

In order to transition to the new validator, all that should be required is to ensure that you have listed `laminas-session` as a composer dependency and replace references to `Laminas\Validator\Csrf::class` with `Laminas\Session\Validator\Csrf::class`.

### `Laminas\Db` Validator Removal

The deprecated "Db" validators that shipped in version 2.0 have been removed. The removed classes are:

- `Laminas\Validator\Db\AbstractDb`
- `Laminas\Validator\Db\NoRecordExists`
- `Laminas\Validator\Db\RecordExists`

### Removal of `Between`, `LessThan` and `GreaterThan` Validators

These validators could theoretically, and indeed were used to perform comparisons on `DateTime` instances, numbers and arbitrary strings.
Whilst these validators worked well for numbers, they worked less well for other data types.

In order to reduce ambiguity, these validators have been replaced by [`NumberComparison`](../validators/number-comparison.md) and [`DateComparison`](../validators/date-comparison.md).

Taking `LessThan` as an example replacement target:

```php
$validator = new Laminas\Validator\LessThan([
    'max' => 10,
    'inclusive' => true,
]);
```

Would become:

```php
$validator = new Laminas\Validator\NumberComparison([
    'max' => 10,
    'inclusiveMax' => true,
]);
```
