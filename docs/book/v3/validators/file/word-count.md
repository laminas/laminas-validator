# WordCount

`Laminas\Validator\File\WordCount` validates that the number of words within a file
match the specified criteria.

## Supported Options

The following set of options are supported:

- `min`: the minimum number of words required; `null` indicates no minimum.
- `max`: the maximum number of words required; `null` indicates no maximum.

## Basic Usage

```php
use Laminas\Validator\File\WordCount;

// Limit the amount of words to a maximum of 2000:
$validator = new WordCount(['max' => 2000]);

// Limit the amount of words to between 100 and 5000:
$validator = new WordCount([
    'min' => 100,
    'max' => 5000,
]);

// Perform validation with file path
if ($validator->isValid('./myfile.txt')) {
    // file is valid
}
```

One of `min` or `max` is required. Omitting both options will cause an exception.
Additionally, the `min` option must be numerically less than the `max` option.

## Validating Uploaded Files

This validator accepts and validates 3 types of argument:

- A string that represents a path to an existing file
- An array that represents an uploaded file as per PHP's [`$_FILES`](https://www.php.net/manual/reserved.variables.files.php) superglobal
- A PSR-7 [`UploadedFileInterface`](https://www.php-fig.org/psr/psr-7/#36-psrhttpmessageuploadedfileinterface) instance
