# Date Comparison Validator

`Laminas\Validator\DateComparison` allows you to validate if a given value is a date that is either:

- Between two pre defined dates
- After a minimum date
- Before a maximum date

By default, comparisons are inclusive.

## Supported options

The following options are supported for `Laminas\Validator\DateComparison`:

- `max` _(string|DateTimeInterface)_: Sets the upper bound for the input.
- `min` _(string|DateTimeInterface)_: Sets the lower bound for the input.
- `inclusiveMin` _(boolean)_: Defines if the validation is inclusive of the lower bound, or exclusive. It defaults to `true`.
- `inclusiveMax` _(boolean)_: Defines if the validation is inclusive of the upper bound, or exclusive. It defaults to `true`.
- `inputFormat` _(string)_: Defines the expected date format if required.

## Min and Max Date Options

The `min` and `max` options when set must be one of the following:

- An object that implements `DateTimeInterface`
- A date string in ISO format, `YYYY-MM-DD`, i.e. '2020-01-31'
- A date and time string in W3C format, `YYYY-MM-DDTHH:MM:SS`, i.e. '2020-01-31T12:34:56'

## Default behaviour

Per default, this validator checks if a value is between `min` and `max` where both upper and lower bounds are considered valid.

```php
$valid  = new Laminas\Validator\DateComparison([
    'min' => '2020-01-01',
    'max' => '2020-12-31',
]);
$value  = '2020-01-01';
$result = $valid->isValid($value);
// returns true
```

In the above example, the result is `true` due to the reason that the default search is inclusive of the border values.
This means in our case that any date between '1st January 2020' to '31st December 2020' is allowed; any other valid date will return `false`.

## Min and Max behaviour

In order to validate a date that is after than a lower bound, either omit the `max` option, or set it explicitly to `null`:

```php
$validator = new Laminas\Validator\DateComparison([
    'min' => '2020-01-01',
    'max' => null,
]);
$validator->isValid('2020-02-03'); // true
```

Conversely, to ensure a date is prior to an upper bound, omit the `min` option or explicitly set it to `null`:

```php
$validator = new Laminas\Validator\DateComparison(['max' => '2020-12-31']);
$validator->isValid('2024-06-07'); // false
```

## Validity of Date inputs

In order to compare dates correctly, the validator converts the input to a `DateTimeInterface` object, therefore, it must be possible to parse string input as a valid date.

Because it is likely that the validator will be paired with some kind of web form, known formats returned by [`<input type="datetime-local">`](https://developer.mozilla.org/en-US/docs/Web/HTML/Element/input/datetime-local) or [`<input type="date">`](https://developer.mozilla.org/en-US/docs/Web/HTML/Element/input/date) are **always supported** without further configuration. For example:

```php
$validator = new Laminas\Validator\DateComparison([
    'min' => '2020-01-01',
]);

$validator->isValid('2020-03-04'); // true
$validator->isValid('2020-01-01T12:34:56'); // true
```

If you have inputs in your application where you expect dates to be provided in a different format such as `l jS F Y`, you can use the `inputFormat` option to specify this:

```php
$validator = new Laminas\Validator\DateComparison([
    'min' => '2020-01-01',
    'inputFormat' => 'l jS F Y',
]);
$validator->isValid('Wednesday 1st January 2020'); // true
```

## Time Zones

Time zones for the `min` and `max` options, and for the validated value are discarded and all dates are compared as UTC date-times.

```php
$africa = new DateTimeZone('Africa/Johannesburg');

$lower = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2020-01-01 10:00:00', $africa);
$upper = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2020-01-01 12:00:00', $africa);

$validator = new Laminas\Validator\DateComparison([
    'min' => $lower,
    'max' => $upper,
]);

$usa = new DateTimeZone('America/New_York');
$input = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2020-01-01 10:45:00', $usa);

$validator->isValid($input); // true
```

In the above example, the validated value is considered as `2020-01-01 10:45:00` in _any_ timezone, and it is between the lower bound of `2020-01-01 10:00:00` and the upper bound of `2020-01-01 12:00:00`
