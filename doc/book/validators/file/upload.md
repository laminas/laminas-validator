# Upload

`Laminas\Validator\File\Upload` validates that a file upload operation was
successful.

## Supported Options

`Laminas\Validator\File\Upload` supports the following options:

- `files`: array of file uploads. This is generally the `$_FILES` array, but
  should be normalized per the details in [PSR-7](http://www.php-fig.org/psr/psr-7/#1-6-uploaded-files)
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
