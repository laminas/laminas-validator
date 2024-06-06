# IsArray Validator

- **Since 2.52.0**

`Laminas\Validator\IsArray` checks that a given value is an array. There are no options.

## Example Usage

```php
$validator = new Laminas\Validator\IsArray();

$validator->isValid('Not an Array'); // false
$validator->isValid(['Any Array']); // true
```
