# Migration from Version 2 to 3

## Changed Behaviour

### Validator Plugin Manager

All aliases that referenced the equivalent, legacy "Zend" validators have been  removed. This means that an exception will be thrown if you attempt to retrieve a validator using one of these aliases such as `Zend\Validator\NotEmpty::class`.

You will need to either update your codebase to use known aliases such as `Laminas\Validator\NotEmpty::class`, or re-implement the aliases in your configuration.

## Changes to Individual Validators

### Digits

This validator no longer uses the Digits filter from `laminas/laminas-filter`, so its static filter property has been removed. This change is unlikely to cause any problems unless for some reason you have extended this class.

## Removed Features

### `Laminas\Db` Validators

The deprecated "Db" validators that shipped in version 2.0 have been removed. The removed classes are:

- `Laminas\Validator\Db\AbstractDb`
- `Laminas\Validator\Db\NoRecordExists`
- `Laminas\Validator\Db\RecordExists`
