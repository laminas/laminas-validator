# IsInstanceOf Validator

`Laminas\Validator\IsInstanceOf` allows you to validate whether a given object is
an instance of a specific class or interface.

## Supported options

The following options are supported for `Laminas\Validator\IsInstanceOf`:

- `className`: Defines the fully-qualified class name which objects must be an
  instance of.

## Basic usage

```php
$validator = new Laminas\Validator\IsInstanceOf([
    'className' => 'Laminas\Validator\Digits'
]);
$object = new Laminas\Validator\Digits();

if ($validator->isValid($object)) {
    // $object is an instance of Laminas\Validator\Digits
} else {
    // false. You can use $validator->getMessages() to retrieve error messages
}
```

If a string argument is passed to the constructor of
`Laminas\Validator\IsInstanceOf`, then that value will be used as the class name:

```php
use Laminas\Validator\Digits;
use Laminas\Validator\IsInstanceOf;

$validator = new IsInstanceOf(Digits::class);
$object = new Digits();

if ($validator->isValid($object)) {
    // $object is an instance of Laminas\Validator\Digits
} else {
    // false. You can use $validator->getMessages() to retrieve error messages
}
```
