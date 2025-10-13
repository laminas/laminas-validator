# Size

`Laminas\Validator\File\Size` checks for the size of a file.

## Supported Options

The following set of options are supported:

- `min`: Minimum file size in integer bytes, or in string SI notation; `null`
  indicates no minimum required.
- `max`: maximum file size in integer bytes, or in string SI notation; `null`
  indicates no maximum required.
- `useByteString`: Boolean flag indicating whether to display error messages
  using SI notation (default, `true`), or in bytes (`false`).

SI units supported are: kB, MB, GB, TB, PB, and EB. All sizes are converted
using 1024 as the base value (ie. 1kB == 1024 bytes, 1MB == 1024kB).

## Basic Usage

```php
use Laminas\Validator\File\Size;

// Limit the file size to 40000 bytes
$validator = new Size(['max' => 40000]);

// Limit the file size to between 10kB and 4MB
$validator = new Size([
    'min' => '10kB',
    'max' => '4MB',
]);

// Perform validation with file path
if ($validator->isValid('./myfile.txt')) {
    // file is valid
}
```

## Validating Uploaded Files

This validator accepts and validates 3 types of argument:

- A string that represents a path to an existing file
- An array that represents an uploaded file as per PHP's [`$_FILES`](https://www.php.net/manual/reserved.variables.files.php) superglobal
- A PSR-7 [`UploadedFileInterface`](https://www.php-fig.org/psr/psr-7/#36-psrhttpmessageuploadedfileinterface) instance
