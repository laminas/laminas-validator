# File Validation Classes

Laminas comes with a set of classes for validating both files and
uploaded files, such as file size validation and CRC checking.

- [Count](count.md)
- [ExcludeExtension](exclude-extension.md)
- [ExcludeMimeType](exclude-mime-type.md)
- [Exists](exists.md)
- [Extension](extension.md)
- [FilesSize](files-size.md)
- [Hash](hash.md)
- [ImageSize](image-size.md)
- [IsCompressed](is-compressed.md)
- [IsImage](is-image.md)
- [MimeType](mime-type.md)
- [NotExists](not-exists.md)
- [Size](size.md)
- [UploadFile](upload-file.md)
- [WordCount](word-count.md)

NOTE: **Validation Argument**
All the File validators' `isValid()` methods support both a file path `string` *or* a `$_FILES` array as the supplied argument.
When a `$_FILES` array is passed in, the `tmp_name` is used for the file path.
