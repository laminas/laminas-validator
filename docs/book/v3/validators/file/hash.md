# Hash

`Laminas\Validator\File\Hash` allows you to validate if a given file's hashed
contents matches the supplied hash(es) and algorithm.

## Supported Options

The following set of options are supported:

- `hash`: String hash or array of hashes against which to test.
- `algorithm`: String hashing algorithm to use; defaults to `crc32`

## Basic Usage

```php
use Laminas\Validator\File\Hash;

// Does file have the given hash?
$validator = new Hash([
    'hash' => '3b3652f',
    'algorithm' => 'crc32',
]);

// Or, check file against multiple hashes
$validator = new Hash([
    'hash' => ['3b3652f', 'e612b69'],
    'algorithm' => 'crc32',
]);

// Perform validation with file path
if ($validator->isValid('./myfile.txt')) {
   // file is valid
}
```

The `algorithm` option must be an algorithm that is available on your installation of PHP. You can find out which algorithms are supported by calling [`hash_algos()`](https://www.php.net/hash_algos).

When supplying a list of hashes to match, the validator will return `true` if _any_ of the hashes match the given file.

## Accepted Uploaded File Types

This validator accepts and validates 3 types of argument:

- A string that represents a path to an existing files
- An array that represents an uploaded file as per PHP's [`$_FILES`](https://www.php.net/manual/reserved.variables.files.php) superglobal
- A PSR-7 [`UploadedFileInterface`](https://www.php-fig.org/psr/psr-7/#36-psrhttpmessageuploadedfileinterface) instance
