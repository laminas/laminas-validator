# NotExists

`Laminas\Validator\File\NotExists` checks for the existence of files in specified
directories.

This validator is inversely related to the [Exists validator](exists.md).

## Supported Options

The following set of options are supported:

- `directory`: Array of directories or comma-delimited string of directories against which to validate.

## Basic Usage

```php
use Laminas\Validator\File\NotExists;

// Only allow files that do not exist in any of the given directories
$validator = new NotExists([
    'directory' => ['/tmp', '/var/tmp'],
]);

if ($validator->isValid('some-file.txt')) {
    // File cannot be found in any of the directories provided
} else {
    // A file with the given name was found
}
```

> ### Checks against All Directories
>
> This validator checks whether the specified file does not exist in **any** of
> the given directories; validation will fail if the file exists in one (or
> more) of the given directories.

## Validating Uploaded Files

This validator accepts and validates 3 types of argument:

- A string that represents a path or a file name
- An array that represents an uploaded file as per PHP's [`$_FILES`](https://www.php.net/manual/reserved.variables.files.php) superglobal
- A PSR-7 [`UploadedFileInterface`](https://www.php-fig.org/psr/psr-7/#36-psrhttpmessageuploadedfileinterface) instance

When provided with either a PSR-7 Uploaded File, or a normalised `$_FILES` upload, if the directory option is unset, validation will fail for successfully uploaded files, however, when a directory option is provided, the given directories are searched for the base name of the uploaded file path.

In the following example, assume `$files` is an array that represents a single, successful file upload in PHP's `$_FILES` array format.

```php
use Laminas\Validator\File\NotExists;

$validator = new NotExists();
$validator->isValid($files); // False - the uploaded file exists (Of course)

$validator = new NotExists([
    'directory' => [
        '/home/files',
        '/tmp',
    ],
]);

if ($validator->isValid($files)) {
    // The basename of the uploaded file could not be found in any of the directories
} else {
    // The basename of the uploaded file was located in at least 1 of the directories
}
```
