# Conditional Validator

`Laminas\Validator\Conditional` allows you to validate a value conditionally depending on the outcome of a user-defined rule.

## Supported options

The following options are supported for `Laminas\Validator\Conditional`:

- `rule`: A callable that will determine whether validation should execute on the value or not
- `validators`: A list of validator specifications used to create a [validator chain](../validator-chains.md)

## About the `$context` parameter of validators

Typically, `laminas-validator` is used via `laminas-inputfilter` which is often, in turn, used via `laminas-form`.
Some validators accept a second parameter to the `isValid()` method that contains the entire payload in an unfiltered and un-validated state.
This parameter `$context` is normally the entire `$_POST` payload.

`laminas-inputfilter` always passes this parameter to the `isValid` method, but, because it is not part of the `ValidatorInterface` contract, it's documentation has often been overlooked.

## Basic usage

The Conditional validator makes use of the `$context` parameter to determine whether validation should execute on the given value.

As such, the `rule` parameter must be a callable that accepts the `$context` array and returns a boolean to indicate whether validation should proceed.

If the rule returns false, the validator will return true, passing validation regardless of the input value. When the rule returns true, validation proceeds and returns either a successful or unsuccessful result depending on the input.

The `Conditional` validator must create validation chains from the given `validators` option, therefore it requires the `ValidatorChainFactory` as a constructor dependency.

Normally, using validators via `laminas-inputfilter` or `laminas-form`, this will not be a concern, but in standalone usage as documented here, we must account for this requirement.

In the following example, we only wish to validate the email address if the `subscribe` field evaluates to a truthy value:

```php
use Laminas\ServiceManager\ServiceManager;
use Laminas\Validator\Conditional;
use Laminas\Validator\EmailAddress;
use Laminas\Validator\ValidatorChainFactory;
use Laminas\Validator\ValidatorPluginManager;

// The chain factory would typically be retrieved from a DI container,
// but instantiated directly here for brevity.
$chainFactory = new ValidatorChainFactory(
    new ValidatorPluginManager(
        new ServiceManager(),
    ),
);

$validator = new Conditional($chainFactory, [
    'rule' => static function (array $context): bool {
        return (bool) ($context['subscribe'] ?? null) === true;
    },
    'validators' => [
        ['name' => EmailAddress::class],
    ],
]);

$postPayload = [
    'subscribe' => '1',
    'email' => 'kermit@example.com',
];

$validator->isValid($postPayload['email'], $postPayload); // true
```

The `rule` option can be any callable, providing it has the signature `callable(array): bool`.

The `validators` option can contain as many validators as required. For more information on the shape of the `validators` option, consult the documentation on the [`ValidatorChainFactory`](../validator-chains.md#the-validator-chain-factory)
