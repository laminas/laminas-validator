# Upload

`Laminas\Validator\File\Upload` validates that a file upload operation was
successful.

## Supported Options

`Laminas\Validator\File\Upload` supports the following options:

- `files`: array of file uploads. This is generally the `$_FILES` array, but
  should be normalized per the details in [PSR-7](http://www.php-fig.org/psr/psr-7/#16-uploaded-files)
  (which is also how [the laminas-http Request](https://docs.laminas.dev/laminas-http)
  normalizes the array).

## Basic Usage

```php
use Laminas\Validator\File\Upload;

// Using laminas-http's request:
$validator = new Upload($request->getFiles());

// Or using options notation:
$validator = new Upload(['files' => $request->getFiles()]);

// Validate:
if ($validator->isValid('foo')) {
    // "foo" file upload was successful
}
```

## PSR-7 Support

- Since 2.11.0

Starting in 2.11.0, you can also pass an array of [PSR-7 UploadedFileInterface](https://www.php-fig.org/psr/psr-7/#16-uploaded-files)
instances to the constructor, the `setFiles()` method, or the `isValid()`
method (in the latter case, you are validating that _all_ uploaded files were
valid).

```php
use Laminas\Validator\File\Upload;

// @var Psr\Http\Message\ServerRequestInterface $request
$validator = new Upload($request->getUploadedFiles());

// Or using options notation:
$validator = new Upload([
    'files' => $request->getUploadedFiles(),
]);

// Validate:
if ($validator->isValid('foo')) {
    // "foo" file upload was successful
}
```
