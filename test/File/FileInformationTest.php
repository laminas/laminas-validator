<?php

declare(strict_types=1);

namespace LaminasTest\Validator\File;

use Laminas\Diactoros\UploadedFile;
use Laminas\Validator\File\FileInformation;
use PHPUnit\Framework\TestCase;

use function chmod;
use function filesize;
use function touch;
use function unlink;

use const UPLOAD_ERR_OK;

/** @psalm-suppress InternalClass,InternalMethod,InternalProperty */
class FileInformationTest extends TestCase
{
    public function testThatANonExistentFileCannotBeCreatedFromAString(): void
    {
        $this->expectExceptionMessage('Cannot detect any file information');
        FileInformation::factory('Foo');
    }

    public function testThatANonExistentFileCannotBeCreatedFromASapiFileArray(): void
    {
        $this->expectExceptionMessage('Cannot detect any file information');
        FileInformation::factory([
            'error'    => UPLOAD_ERR_OK,
            'tmp_name' => 'Foo',
            'name'     => 'Foo',
            'type'     => 'text/plain',
        ]);
    }

    public function testExpectedValuesForFilePath(): void
    {
        $path = __DIR__ . '/_files/picture.jpg';
        $file = FileInformation::factory($path);

        self::assertSame($path, $file->path);
        self::assertNull($file->clientFileName);
        self::assertNull($file->clientMediaType);
        self::assertTrue($file->readable);
        self::assertSame('image/jpeg', $file->detectMimeType());
        self::assertSame('picture.jpg', $file->baseName);
    }

    public function testExpectedValuesForUploadedFile(): void
    {
        $path = __DIR__ . '/_files/picture.jpg';

        $upload = new UploadedFile(
            $path,
            filesize($path),
            UPLOAD_ERR_OK,
            'Foo.jpg',
            'image/jpg',
        );

        $file = FileInformation::factory($upload);

        self::assertSame($path, $file->path);
        self::assertSame('Foo.jpg', $file->clientFileName);
        self::assertSame('image/jpg', $file->clientMediaType);
        self::assertTrue($file->readable);
        self::assertSame('image/jpeg', $file->detectMimeType());
        self::assertSame('picture.jpg', $file->baseName);
    }

    public function testExpectedValuesForSapiFilesArray(): void
    {
        $path = __DIR__ . '/_files/picture.jpg';

        $upload = [
            'tmp_name' => $path,
            'size'     => filesize($path),
            'error'    => UPLOAD_ERR_OK,
            'name'     => 'Foo.jpg',
            'type'     => 'image/jpg',
        ];

        $file = FileInformation::factory($upload);

        self::assertSame($path, $file->path);
        self::assertSame('Foo.jpg', $file->clientFileName);
        self::assertSame('image/jpg', $file->clientMediaType);
        self::assertTrue($file->readable);
        self::assertSame('image/jpeg', $file->detectMimeType());
        self::assertSame('picture.jpg', $file->baseName);
    }

    public function testUnReadableFile(): void
    {
        $path = __DIR__ . '/_files/no-read.txt';
        touch($path);
        chmod($path, 0333);
        try {
            $file = FileInformation::factory($path);
            self::assertFalse($file->readable);
        } finally {
            unlink($path);
        }
    }
}
