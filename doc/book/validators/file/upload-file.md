# UploadFile

`Laminas\Validator\File\UploadFile` checks whether a single file has been uploaded
via a form `POST` and will return descriptive messages for any upload errors.

# Basic Usage

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

## Usage with laminas-inputfilter

When using laminas-inputfilter's [FileInput](https://docs.laminas.dev/laminas-inputfilter/file-input/),
this validator will be automatically prepended to the validator chain.
