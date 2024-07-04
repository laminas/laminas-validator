# IsJsonString

`Laminas\Validator\IsJsonString` allows you to validate whether a given value is a string that will be successfully decoded by `json_decode`.

## Basic Usage

```php
$validator = new Laminas\Validator\IsJsonString();
$input = '{"some":"json"}';

if ($validator->isValid($input)) {
    // $input can be successfully decoded
} else {
    // $input is not a valid JSON string
}
```

## Restricting Acceptable JSON Types

`json_decode` accepts numeric strings representing integers and floating point numbers, booleans, arrays and objects.
You can restrict what is considered valid input using the `allow` option of the validator.

```php
use Laminas\Validator\IsJsonString;

$validator = new IsJsonString([
    'allow' => IsJsonString::ALLOW_ALL ^ IsJsonString::ALLOW_BOOL,
]);

$validator->isValid('true'); // false
```

The `allow` option is a bit mask of the `ALLOW_*` constants in `IsJsonString`:

- `IsJsonString::ALLOW_INT` - Accept integer such as `1`
- `IsJsonString::ALLOW_FLOAT` - Accept floating-point value such as `1.234`
- `IsJsonString::ALLOW_BOOL` - Accept `true` and `false`
- `IsJsonString::ALLOW_ARRAY` - Accept JSON arrays such as `["One", "Two"]`
- `IsJsonString::ALLOW_OBJECT` - Accept JSON objects such as `{"Some":"Object"}`
- `IsJsonString::ALLOW_ALL` - A convenience constant allowing all of the above _(Also the default)_.

## Restricting Max Object or Array Nesting Level

If you wish to restrict the nesting level of arrays and objects that are considered valid, the validator accepts a `maxDepth` option. The default value of this option is `512` - the same default value as `json_decode`.

```php
$validator = new Laminas\Validator\IsJsonString(['maxDepth' => 2]);
$validator->isValid('{"nested": {"object: "here"}}'); // false
```
