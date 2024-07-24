# Migration from Version 2 to 3

## Changed Behaviour & Signature Changes

### Final Classes

In order to reduce maintenance burden and to help users to favour [composition over inheritance](https://en.wikipedia.org/wiki/Composition_over_inheritance), all classes, where possible have been marked as final.

### `AbstractValidator` Breaking Changes

There have been a number of significant, breaking changes to the `AbstractValidator` which _all_ shipped validators inherit from.

The following methods have been removed affecting all the shipped validators in addition to [individual changes](#changes-to-individual-validators) to those validators:

- `getOption`
- `getOptions`
- `setOptions`
- `getMessageVariables`
- `getMessageTemplates`
- `setMessages`
- `setValueObscured`
- `isValueObscured`
- `getDefaultTranslator`
- `hasDefaultTranslator`
- `getDefaultTranslatorTextDomain`
- `getMessageLength`

#### Validator Options

It is now necessary to pass all options to the validator constructor.
This means that you cannot create a validator instance in an invalid state.
It also makes it impossible to change the option values after the validator has been instantiated.

Removal of the various option "getters" and "setters" are likely to cause a number of breaking changes to inheritors of `AbstractValidator` _(i.e. custom validators you may have written)_ so we have provided an [example refactoring](refactoring-legacy-validators.md) to illustrate the necessary changes.

### Validator Plugin Manager

#### Removal of legacy Zend aliases

All aliases that referenced the equivalent, legacy "Zend" validators have been  removed. This means that an exception will be thrown if you attempt to retrieve a validator using one of these aliases such as `Zend\Validator\NotEmpty::class`.

You will need to either update your codebase to use known aliases such as `Laminas\Validator\NotEmpty::class`, or re-implement the aliases in your configuration.

#### Removal of Service Manager v2 canonical FQCNs

There are a number of aliases left over from early versions of Service Manager where each validator would be aliased by a lowercase, normalized string such as `laminasvalidatoremail` to represent `Laminas\Validator\Email::class`. All of these aliases have been removed.

#### Removal of Laminas\i18n aliases and factories

The [`laminas-i18n`](https://docs.laminas.dev/laminas-i18n/validators/introduction/) component ships a number of validators that historically have been pre-configured from within this component. These aliases and factory entries have been removed.

[Removal of the aliases](https://github.com/laminas/laminas-validator/commit/5bbfe8baeba48f3b77c909a8d6aa930c1d2897b7) here is unlikely to cause any issues, providing you have enabled the `ConfigProvider` or `Module` from `laminas-i18n` in your application.

### Required Options at Construction Time

A number of validators now require options during construction now that runtime mutation of validator settings is no longer possible _(Described in ['General Changes'](#general-changes) below)_.

Validators that require options will now throw an exception when the relevant option is not provided. For example, the [`Regex` validator](#laminasvalidatorregex) requires a `pattern` option.

The affected validators are:

- `Laminas\Validator\Barcode`
- `Laminas\Validator\Bitwise`
- `Laminas\Validator\Callback`
- `Laminas\Validator\DateComparison`
- `Laminas\Validator\Explode`
- `Laminas\Validator\InArray`
- `Laminas\Validator\IsInstanceOf`
- `Laminas\Validator\NumberComparison`
- `Laminas\Validator\Regex`
- `Laminas\Validator\File\ExcludeExtension`
- `Laminas\Validator\File\ExcludeMimeType`
- `Laminas\Validator\File\Extension`
- `Laminas\Validator\File\FilesSize`
- `Laminas\Validator\File\Hash`
- `Laminas\Validator\File\ImageSize`
- `Laminas\Validator\File\MimeType`
- `Laminas\Validator\File\Size`
- `Laminas\Validator\File\WordCount`

## Changes to Individual Validators

### General Changes

#### Removal of "Getters" and "Setters"

In general, most validators no longer have "getters" and "setters" for options and configuration.

Taking the `Regex` validator as an example, in the 2.x series, it was possible to create a regex validator and then configure it _after_ it had been constructed.
This allows the creation of validator instances with an invalid state, or configuration.

Removing getters and setters forces us to provide valid configuration of the validator instance at construction time, vastly reducing the API surface and closing off potential sources of bugs.

#### Consistent Construction via an Array of Options

In the 2.x series, _most_ validators accepted a range of constructor arguments, for example, a single options array, an `ArrayAccess` or `Traversable` and frequently variadic arguments of the most important configuration parameters.

Generally speaking, validators now only accept associative arrays with improved documentation of exactly which options are available.

### `Laminas\Validator\Barcode`

The following methods have been removed:

- `getAdapter`
- `setAdapter`
- `getChecksum`
- `useChecksum`

Behaviour changes:

- The constructor now only accepts an associative array of documented options.
- The `adapter` option can now be a FQCN - previously it had to be an instance, or an unqualified class name.

#### Significant Changes to Adapters

Inheritance has changed for all the shipped barcode adapters. None of the adapters extend from the **now removed** `AbstractAdapter` and instead, all adapters implement the methods expected by `Laminas\Validator\Barcode\AdapterInterface`. The interface itself now only specifies 4 methods:

- `hasValidLength`
- `hasValidCharacters`
- `hasValidChecksum`
- `getLength`

The documentation on [writing custom adapters](../validators/barcode.md#writing-custom-adapters) has been updated to reflect these changes.

### `Laminas\Validator\Bitwise`

The following methods have been removed:

- `setControl`
- `getControl`
- `setOperator`
- `getOperator`
- `setStrict`
- `getStrict`

Behaviour changes:

- The constructor now only accepts an associative array of documented options
- Validation will now fail if the input is not `interger-ish` i.e. `int` or `int-string`

### `Laminas\Validator\Callback`

The following methods have been removed:

- `setCallback`
- `getCallback`
- `setCallbackOptions`
- `getCallbackOptions`

A new option `bind` has been added that will bind the given callback to the scope of the validator so that you can manipulate error messages from within the callback itself.

The [documentation](../validators/callback.md#callbacks-and-scope) has been updated with the relevant details.

### `Laminas\Validator\CreditCard`

The following methods have been removed:

- `getType`
- `setType`
- `addType`
- `getService`
- `setService`

Behaviour changes:

- The constructor now only accepts an associative array of [documented options](../validators/credit-card.md).

### `Laminas\Validator\Date`

The following methods have been removed:

- `getFormat`
- `setFormat`
- `isStrict`
- `setStrict`

Behaviour changes:

- The constructor now only accepts an associative array of [documented options](../validators/date.md).

### `Laminas\Validator\DateStep`

The following methods have been removed:

- `getFormat`
- `setFormat`
- `isStrict`
- `setStrict`
- `getBaseValue`
- `setBaseValue`
- `getStep`
- `setStep`
- `getTimezone`
- `setTimezone`

Behaviour changes:

- The constructor now only accepts an associative array.
- The default format has changed to use `DateTimeInterface::ATOM` instead of the deprecated `DateTimeInterface::ISO8601`

### `Laminas\Validator\Digits`

This validator no longer uses the Digits filter from `laminas/laminas-filter`, so its static filter property has been removed. This change is unlikely to cause any problems unless for some reason you have extended this class.

### `Laminas\Validator\EmailAddress`

The following methods have been removed:

- `getHostnameValidator`
- `setHostnameValidator`
- `getAllow`
- `setAllow`
- `isMxSupported`
- `getMxCheck`
- `useMxCheck`
- `getDeepMxCheck`
- `useDeepMxCheck`
- `getDomainCheck`
- `useDomainCheck`

Behaviour changes:

- The constructor now only accepts an associative array of [documented options](../validators/email-address.md).
- If you use custom `Hostname` validators to restrict valid host types, it is worth [reading the documentation](../validators/email-address.md#controlling-hostname-validation-options) about how the Email Address validator interacts with the Hostname validator with regard to option priority for the `allow` option.

### `Laminas\Validator\Explode`

The following methods have been removed:

- `setValidator`
- `getValidator`
- `setValueDelimiter`
- `getValueDelimiter`
- `setValidatorPluginManager`
- `getValidatorPluginManager`
- `setBreakOnFirstFailure`
- `isBreakOnFirstFailure`

Behaviour changes:

- Non-string input will now cause a validation failure
- The composed validator can now be specified as a FQCN
- The constructor now only accepts an associative array
- Error messages match the same format as other validators, i.e. `array<string, string>`

### `Laminas\Validator\Hostname`

The following methods have been removed:

- `getIpValidator`
- `setIpValidator`
- `getAllow`
- `setAllow`
- `getIdnCheck`
- `useIdnCheck`
- `getTldCheck`
- `useTldCheck`

Behaviour changes:

- The constructor now only accepts an associative array of [documented options](../validators/hostname.md).

### `Laminas\Validator\Iban`

The following methods have been removed:

- `getCountryCode`
- `setCountryCode`
- `allowNonSepa`
- `setAllowNonSepa`

Behaviour changes:

- The constructor now only accepts an associative array of [documented options](../validators/iban.md).

### `Laminas\Validator\Identical`

The following methods have been removed:

- `getToken`
- `setToken`
- `getStrict`
- `setStrict`
- `getLiteral`
- `setLiteral`

Behaviour changes:

- The constructor now only accepts an associative array of [documented options](../validators/identical.md).

### `Laminas\Validator\InArray`

The following methods have been removed:

- `getHaystack`
- `setHaystack`
- `getStrict`
- `setStrict`
- `getRecursive`
- `setRecursive`

Behaviour changes:

- The constructor now only accepts an associative array of [documented options](../validators/in-array.md).

### `Laminas\Validator\Ip`

Behaviour changes:

- The constructor now only accepts an associative array of [documented options](../validators/ip.md).

### `Laminas\Validator\Isbn`

The following methods have been removed:

- `setSeparator`
- `getSeparator`
- `setType`
- `getType`

Behaviour changes:

- The constructor now only accepts an associative array of [documented options](../validators/isbn.md).
- The `separator` option has been removed. Instead of requiring users to provide the expected separator, all valid separators are now stripped from the input prior to validation. With the default option for auto-detection of ISBN-10 and ISBN-13 formats, the validator is greatly simplified at the point of use.
- Previously, the classes `Laminas\Validator\Isbn\Isbn10` and `Laminas\Validator\Isbn\Isbn13` were used to validate each format. The code in these classes is now inlined inside the validator so these classes have been removed. 

### `Laminas\Validator\IsCountable`

The following methods have been removed:

- `getCount`
- `setCount`
- `getMin`
- `setMin`
- `getMax`
- `setMax`

Behaviour changes:

- The constructor now only accepts an associative array of [documented options](../validators/is-countable.md).

### `Laminas\Validator\IsInstanceOf`

The following methods have been removed:

- `getClassName`
- `setClassName`

Behaviour changes:

- The constructor now only accepts an associative array of [documented options](../validators/isinstanceof.md).

### `Laminas\Validator\IsJsonString`

The following methods have been removed:

- `setAllow`
- `setMaxDepth`

Behaviour changes:

- The constructor now only accepts an associative array of [documented options](../validators/is-json-string.md).

### `Laminas\Validator\NotEmpty`

The following methods have been removed:

- `getType`
- `setType`
- `getDefaultType`

Behaviour changes:

- The constructor now only accepts an associative array of [documented options](../validators/not-empty.md).

### `Laminas\Validator\Regex`

The following methods have been removed:

- `setPattern`
- `getPattern`

Behaviour changes:

- Non string input will now fail validation. Previously, scalars would be cast to string before pattern validation leading to possible bugs, for example, floating point numbers could be cast to scientific notation.
- Now the pattern is a required option in the constructor, an invalid pattern will cause an exception during `__construct` instead of during validation.
- The single constructor argument must now be either an associative array of options, or the regex pattern as a string.

### `Laminas\Validator\Step`

The following methods have been removed:

- `setBaseValue`
- `getBaseValue`
- `setStep`
- `getStep`

Behaviour changes:

- The constructor now only accepts an associative array of [documented options](../validators/step.md).

### `Laminas\Validator\StringLength`

The following methods have been removed:

- `setMin`
- `getMin`
- `setMax`
- `getMax`
- `getStringWrapper`
- `setStringWrapper`
- `getEncoding`
- `setEncoding`
- `getLength`
- `setLength`

Behaviour changes:

- The constructor now only accepts an associative array of options.
- Malformed multibyte input is now handled more consistently: In the event that any of the string wrappers cannot reliably detect the length of a string, an exception will be thrown.

### `Laminas\Validator\Timezone`

The following methods have been removed

- `setType`

Behaviour changes:

- The constructor now only accepts an associative array of documented options
- The `type` option can now only be one of the type constants declared on the class, i.e. `Timezone::ABBREVIATION`, `Timezone::LOCATION`, or `Timezone::ALL`
- When validating timezone abbreviations, the check is now case-insensitive, so `CET` will pass validation when previously it did not.

### `Laminas\Validator\Uri`

The following methods have been removed:

- `setUriHandler`
- `getUriHandler`
- `setAllowAbsolute`
- `getAllowAbsolute`
- `setAllowRelative`
- `getAllowRelative`

Behaviour changes:

- The constructor now only accepts an associative array of [documented options](../validators/uri.md).

### `Laminas\Validator\File\Count`

The following methods have been removed:

- `getMin`
- `setMin`
- `getMax`
- `setMax`
- `addFile`

Behaviour changes:

- The constructor now only accepts an associative array of [documented options](../validators/file/count.md)
- Compatibility with the legacy `Laminas\File\Transfer` api has been removed

### `Laminas\Validator\File\ExcludeExtension` and `Laminas\Validator\File\Extension`

`ExcludeExtension` no longer inherits from the `Extension` validator.

The following methods have been removed:

- `getCase`
- `setCase`
- `getExtension`
- `setExtension`
- `addExtension`
- `getAllowNonExistentFile`
- `setAllowNonExistentFile`

Behaviour changes:

- The constructor now only accepts an associative array of [documented options](../validators/file/extension.md)
- Compatibility with the legacy `Laminas\File\Transfer` api has been removed
- An additional validation failure condition has been added for situations where the input cannot be recognised as either a file path or some type of upload.

### `Laminas\Validator\File\ExcludeMimeType`, `Laminas\Validator\File\MimeType`, `Laminas\Validator\File\IsCompressed` and `Laminas\Validator\File\IsImage`

The following methods have been removed:

- `getMagicFile`
- `setMagicFile`
- `disableMagicFile`
- `isMagicFileDisabled`
- `getHeaderCheck`
- `enableHeaderCheck`
- `getMimeType`
- `setMimeType`
- `addMimeType`

Behaviour changes:

- The constructor now only accepts an associative array of [documented options](../validators/file/mime-type.md)
- Compatibility with the legacy `Laminas\File\Transfer` api has been removed
- The options `enableHeaderCheck`, `disableMagicFile` and `magicFile` have been removed. A custom magic file is now no longer accepted or used, instead the magic file bundled with PHP is used instead.

### `Laminas\Validator\File\Size` and `Laminas\Validator\File\FilesSize`

`FilesSize` no longer inherits from the `Size` validator.

The following methods have been removed:

- `useByteString`
- `getByteString`
- `getMin`
- `setMin`
- `getMax`
- `setMax`
- `getSize`
- `setSize`

Behaviour changes:

- The constructor now only accepts an associative array of [documented options](../validators/file/size.md)
- Compatibility with the legacy `Laminas\File\Transfer` api has been removed

### `Laminas\Validator\File\Hash`

The following methods have been removed:

- `getHash`
- `setHash`
- `addHash`

Behaviour changes:

- The constructor now only accepts an associative array of [documented options](../validators/file/hash.md)
- Compatibility with the legacy `Laminas\File\Transfer` api has been removed
- Also see information about the [removal of inheritors](#removal-of-laminasvalidatorfilehash-inheritors)

### `Laminas\Validator\File\Exists` and `Laminas\Validator\File\NotExists`

The following methods have been removed:

- `getDirectory`
- `setDirectory`
- `addDirectory`

Behaviour changes:

- The constructor now only accepts an associative array of [documented options](../validators/file/exists.md)
- Compatibility with the legacy `Laminas\File\Transfer` api has been removed
- `NotExists` no longer inherits from `Exists`

### `Laminas\Validator\File\ImageSize`

The following methods have been removed:

- `getMinWidth`
- `setMinWidth`
- `getMaxWidth`
- `setMaxWidth`
- `getMinHeight`
- `setMinHeight`
- `getMaxHeight`
- `setMaxHeight`
- `getImageMin`
- `setImageMin`
- `getImageMax`
- `setImageMax`
- `getImageWidth`
- `setImageWidth`
- `getImageHeight`
- `setImageHeight`

Behaviour changes:

- The constructor now only accepts an associative array of [documented options](../validators/file/image-size.md)
- Compatibility with the legacy `Laminas\File\Transfer` api has been removed

### `Laminas\Validator\File\WordCount`

The following methods have been removed:

- `getMin`
- `setMin`
- `getMax`
- `setMax`

Behaviour changes:

- The constructor now only accepts an associative array of [documented options](../validators/file/word-count.md)
- Compatibility with the legacy `Laminas\File\Transfer` api has been removed

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

### Removal of `Laminas\Validator\File\Upload`

The deprecated `Upload` validator was only capable of validating the `$_FILES` super global, providing the entire `$_FILES` array was provided, at runtime, prior to validation. The validator expected a key such as `my-upload` corresponding to a posted form element and also relied on the legacy and deprecated `Laminas\File\Transfer` api.

We suggest that you look at the [`UploadFile`](../validators/file/upload-file.md) validator instead.

### Removal of `Laminas\Validator\File\Hash` inheritors

The following classes have been removed:

- `Laminas\Validator\File\Crc32`
- `Laminas\Validator\File\Md5`
- `Laminas\Validator\File\Sha1`

These inheritors of the `Hash` validator were unnecessary. Simply construct an instance of the `Hash` validator with the algorithm that you require, for example:

```php
$hash = new \Laminas\Validator\File\Hash([
    'hash' => 'SomeExpectedSha256Hash',
    'algorithm' => 'sha256',
]);

$hash->isValid('/path/to/file.md');
```

The algorithms available are dictated by your installation of PHP and can be determined with [`hash_algos()`](https://www.php.net/manual/function.hash-algos.php) 
