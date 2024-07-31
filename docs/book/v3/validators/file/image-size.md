# ImageSize

`Laminas\Validator\File\ImageSize` checks the size of image files. Minimum and/or
maximum dimensions can be set to validate against.

## Supported Options

The following set of options are supported:

- `minWidth`: Set the minimum image width as an integer; `null` (the default)
  indicates no minimum.
- `minHeight`: Set the minimum image height as an integer; `null` (the default)
  indicates no minimum.
- `maxWidth`: Set the maximum image width as an integer; `null` (the default)
  indicates no maximum.
- `maxHeight`: Set the maximum image height as an integer; `null` (the default)
  indicates no maximum.

## Basic Usage

```php
use Laminas\Validator\File\ImageSize;

// Is image size between 320x200 (min) and 640x480 (max)?
$validator = new ImageSize([
    'minWidth' => 320,
    'minHeight' => 200,
    'maxWidth' => 640,
    'maxHeight' => 480,
]);

// Is image size equal to or larger than 320x200?
$validator = new ImageSize([
    'minWidth' => 320,
    'minHeight' => 200,
]);

// Is image size equal to or smaller than 640x480?
$validator = new ImageSize([
    'maxWidth' => 640,
    'maxHeight' => 480,
]);

// Perform validation with file path
if ($validator->isValid('./myfile.jpg')) {
    // file is valid
}
```

## Validating Uploaded Files

This validator accepts and validates 3 types of argument:

- A string that represents a path to an existing file
- An array that represents an uploaded file as per PHP's [`$_FILES`](https://www.php.net/manual/reserved.variables.files.php) superglobal
- A PSR-7 [`UploadedFileInterface`](https://www.php-fig.org/psr/psr-7/#36-psrhttpmessageuploadedfileinterface) instance
