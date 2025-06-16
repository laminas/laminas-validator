# Backed Enum Value Validator

`Laminas\Validator\BackedEnumValue` allows you to validate that a string or numeric value is a valid value for a specified [enum](https://www.php.net/manual/language.enumerations.php).

## Supported Options

The following options are supported for `Laminas\Validator\BackedEnumValue`:

- `enum`: The backed enum class you wish to test against

## Basic Usage

```php
enum MyEnum: string {
    case Something = 'Some Value';
    case OtherThing = 'Other Value';
}

$validator = new Laminas\Validator\BackedEnumValue([
    'enum' => MyEnum::class,
]);

if ($validator->isValid('Some Value')) {
    // $value is a valid value for `MyEnum`
} else {
    // $value is not a known value in `MyEnum`
    foreach ($validator->getMessages() as $message) {
        echo "$message\n";
    }
}
```

[Unit enums](https://www.php.net/manual/language.enumerations.basics.php) are not supported by this validator. To validate enum cases as opposed to _values_, see the [`EnumCase` validator](enum-case.md).
