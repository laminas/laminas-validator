# MimeType

`Laminas\Validator\File\MimeType` checks the MIME type of files. It will assert
`true` when a given file matches any defined MIME type.

This validator is inversely related to the
[ExcludeMimeType validator](exclude-mime-type.md)

NOTE: **Compatibility**
This component makes use of the [`FileInfo`](https://www.php.net/manual/book.fileinfo.php) extension which is enabled by default in PHP.
If you are using a version of PHP that was explicitly built without the File Info extension, this validator will not work.

## Supported Options

The following set of options are supported:

- `mimeType`: Comma-delimited string of MIME types, or array of MIME types,
  against which to test. Types can be specific (e.g., `image/jpeg`), or refer
  only to the group (e.g., `image`).

## Basic Usage

```php
use Laminas\Validator\File\MimeType;

// Only allow 'gif' or 'jpg' files
$validator = new MimeType(['mimeType' => 'image/gif,image/jpeg']);

// ... or with array notation:
$validator = new MimeType(['mimeType' => ['image/gif', 'image/jpeg']]);

// ... or restrict to  entire group of types:
$validator = new MimeType(['mimeType' => ['image', 'audio']]);

// Perform validation
if ($validator->isValid('./myfile.jpg')) {
    // file is valid
}
```

WARNING: **Validating MIME Groups Is Potentially Dangerous**
Allowing "groups" of MIME types will accept **all** members of this group, even if your application does not support them.
For instance, When you allow`image` you also allow `image/xpixmap` and `image/vasa`, both of which could be problematic.

## Validating Uploaded Files

This validator accepts and validates 3 types of argument:

- A string that represents a path to an existing file
- An array that represents an uploaded file as per PHP's [`$_FILES`](https://www.php.net/manual/reserved.variables.files.php) superglobal
- A PSR-7 [`UploadedFileInterface`](https://www.php-fig.org/psr/psr-7/#36-psrhttpmessageuploadedfileinterface) instance
