# Extension

`Laminas\Validator\File\Extension` checks the extension of files. It will assert
`true` when a given file matches any of the defined extensions.

This validator is inversely related to the
[ExcludeExtension validator](exclude-extension.md).

## Supported Options

The following set of options are supported:

- `extension`: Array of extensions, or comma-delimited string of extensions,
  against which to test.
- `case`: Boolean indicating whether extensions should match case
  sensitively; defaults to `false` (case-insensitive).
- `allowNonExistentFile`: Boolean indicating whether
  to allow validating a filename for a non-existent file. Defaults to `false`
  (will not validate non-existent files).

## Usage Examples

```php
use Laminas\Validator\File\Extension;

$validator = new Extension([
    'extension' => ['php', 'exe'],
]);

$validator->isValid('./file.php'); // true
$validator->isValid('./file.PHP'); // true

$validator = new Extension([
    'extension' => 'php,exe',
    'case' => true,
]);

$validator->isValid('./file.php'); // true
$validator->isValid('./file.PHP'); // false
```

### Validating Arbitrary Filenames

```php
use Laminas\Validator\File\Extension;

$validator = new Extension([
    'extension' => 'gif,jpg,png',
    'allowNonExistentFile' => true,
]);

$validator->isValid('picture.jpg'); // true
$validator->isValid('something-else.txt'); // false
```

## Validating Uploaded Files

This validator accepts and validates 3 types of argument:

- A string that represents a path or a filename
- An array that represents an uploaded file as per PHP's [`$_FILES`](https://www.php.net/manual/reserved.variables.files.php) superglobal
- A PSR-7 [`UploadedFileInterface`](https://www.php-fig.org/psr/psr-7/#36-psrhttpmessageuploadedfileinterface) instance
