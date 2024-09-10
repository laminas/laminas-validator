# Exists

`Laminas\Validator\File\Exists` checks for the existence of files in specified
directories.

This validator is inversely related to the [NotExists validator](not-exists.md).

## Supported Options

The following set of options are supported:

- `directory`: Array of directories, or comma-delimited string of directories.
- `all`: A boolean that when `true` _(default)_ requires that the filename is present in **all** the listed directories

## Usage Examples

```php
use Laminas\Validator\File\Exists;

// Only allow files that exist in ~both~ directories
$validator = new Exists(['directory' => '/tmp,/var/tmp']);

if ($validator->isValid('myfile.txt')) {
    // file is present in all directories
} else {
    // file was not present in at least 1 of the directories
}
```

```php
use Laminas\Validator\File\Exists;

// Allow files that exist in any of the listed directories
$validator = new Exists([
    'directory' => ['/tmp', '/var/tmp'],
    'all' => false,
]);

if ($validator->isValid('myfile.txt')) {
    // file was found in at least 1 directory
} else {
    // file was not found in one of the directories
}
```

```php
use Laminas\Validator\File\Exists;

// Check the value for existence without a directory option
$validator = new Exists();

if ($validator->isValid('/path/to/myfile.txt')) {
    // file exists
}
```

> ### Checks against All Directories
>
> By default, this validator checks whether the specified file exists in **all** of the
> given directories; validation will fail if the file does not exist in one
> or more of them. To change this behaviour, be sure to set the `all` option to false.

## Validating Uploaded Files

This validator accepts and validates 3 types of argument:

- A string that represents a path or a file name
- An array that represents an uploaded file as per PHP's [`$_FILES`](https://www.php.net/manual/reserved.variables.files.php) superglobal
- A PSR-7 [`UploadedFileInterface`](https://www.php-fig.org/psr/psr-7/#36-psrhttpmessageuploadedfileinterface) instance

Without a `directory` option, the validator will check any of the listed argument types to ensure they exist.
For example, without a `directory` option, any successful upload will be deemed valid.
