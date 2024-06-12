# UploadFile

`Laminas\Validator\File\UploadFile` checks whether a single file has been uploaded
via a form `POST` and will return descriptive messages for any upload errors.

## Basic Usage

```php
use Laminas\Http\PhpEnvironment\Request;
use Laminas\Validator\File\UploadFile;

$request = new Request();
$files   = $request->getFiles();
// i.e. $files['my-upload']['error'] == 0

$validator = new UploadFile();
if ($validator->isValid($files['my-upload'])) {
    // file is valid
}
```

## PSR-7 Support

- Since 2.11.0

Starting in 2.11.0, you can also pass [PSR-7 UploadedFileInterface](https://www.php-fig.org/psr/psr-7/#uploadedfileinterface)
instances as values to the validator. When valid, `getValue()` will return the
instance validated verbatim:

```php
$validator = new UploadFile();

// @var Psr\Http\Message\UploadedFileInterface $uploadedFile
if ($validator->isValid($uploadedFile)) {
    // file is valid
    $validInstance = $validator->getValue(); // === $uploadedFile
}
```

## Usage with laminas-inputfilter

When using laminas-inputfilter's [FileInput](https://docs.laminas.dev/laminas-inputfilter/file-input/),
this validator will be automatically prepended to the validator chain.
