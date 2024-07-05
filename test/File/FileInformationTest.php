<?php

declare(strict_types=1);

namespace LaminasTest\Validator\File;

use Laminas\Diactoros\UploadedFile;
use Laminas\Validator\File\FileInformation;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use function chmod;
use function filesize;
use function touch;
use function unlink;

use const PHP_INT_MAX;
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
            'size'     => 0,
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
        self::assertSame(filesize($path), $file->size());
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
        self::assertSame(filesize($path), $file->size());
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
        self::assertSame(filesize($path), $file->size());
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

    /** @return list<array{0: int, 1: string}> */
    public static function bytesToSiUnitDataProvider(): array
    {
        return [
            [10, '10B'],
            [1536, '1.5kB'],
            [2_621_440, '2.5MB'],
            [1_073_741_824, '1GB'],
            [6_442_450_944, '6GB'],
            [6_597_069_766_656, '6TB'],
            [6_755_399_441_055_744, '6PB'],
        ];
    }

    #[DataProvider('bytesToSiUnitDataProvider')]
    public function testBytesToSiUnit(int $input, string $expect): void
    {
        self::assertSame($expect, FileInformation::bytesToSiUnit($input));
    }

    public static function siUnitToBytesProvider(): array
    {
        return [
            [10, '10b'],
            [1536, '1.5kB'],
            [2_621_440, '2.5MB'],
            [1_073_741_824, '1GB'],
            [6_442_450_944, '6GB'],
            [6_597_069_766_656, '6TB'],
            [10, '10 b'],
            [1536, '1.5 kB'],
            [1536, '1.5 kb'],
            [2_621_440, '2.5 MB'],
            [1_073_741_824, '1 GB'],
            [6_442_450_944, '6 GB'],
            [6_597_069_766_656, '6 TB'],
            [6_755_399_441_055_744, '6 PB'],
            [8_070_450_532_247_928_832, '7EB'],
            [PHP_INT_MAX, '8EB'],
            [PHP_INT_MAX, '1ZB'],
            [PHP_INT_MAX, '10YB'],
        ];
    }

    #[DataProvider('siUnitToBytesProvider')]
    public function testSiUnitToBytes(int $expect, string $input): void
    {
        self::assertSame($expect, FileInformation::siUnitToBytes($input));
    }
}
