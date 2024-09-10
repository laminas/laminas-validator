# Enum Case Validator

`Laminas\Validator\EnumCase` allows you to validate that a string is a valid case for a specified [enum](https://www.php.net/manual/language.enumerations.php).

## Supported Options

The following options are supported for `Laminas\Validator\BackedEnumValue`:

- `enum`: The backed or unit enum class you wish to test against

## Basic Usage

```php
enum MyEnum {
    case Something;
    case OtherThing;
}

$validator = new Laminas\Validator\EnumCase([
    'enum' => MyEnum::class,
]);

if ($validator->isValid('Something')) {
    // $value is a valid case for `MyEnum`
} else {
    // $value is not a known case in `MyEnum`
    foreach ($validator->getMessages() as $message) {
        echo "$message\n";
    }
}
```

To validate against the _values_ of a backed enum, see the [`BackedEnumValue` validator](backed-enum-value.md).
