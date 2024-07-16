<?php

declare(strict_types=1);

namespace LaminasTest\Validator\File;

use Laminas\Diactoros\UploadedFile;
use Laminas\Validator\Exception\InvalidArgumentException;
use Laminas\Validator\File\Count;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use const UPLOAD_ERR_NO_FILE;
use const UPLOAD_ERR_OK;

/** @psalm-import-type OptionsArgument from Count */
final class CountTest extends TestCase
{
    public function testMinCannotExceedMax(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The `min` option cannot exceed the `max` option');

        new Count(['min' => 10, 'max' => 5]);
    }

    /**
     * @return iterable<string, array{
     *     0: OptionsArgument,
     *     1: mixed,
     *     2: bool,
     *     3: string|null,
     * }>
     */
    public static function basicDataProvider(): iterable
    {
        yield 'Not enough files (paths)' => [
            ['min' => 10],
            [__DIR__ . '/_files/picture.jpg', __DIR__ . '/_files/test.zip'],
            false,
            Count::TOO_FEW,
        ];

        yield 'Too many files (paths)' => [
            ['max' => 1],
            [__DIR__ . '/_files/picture.jpg', __DIR__ . '/_files/test.zip'],
            false,
            Count::TOO_MANY,
        ];

        yield 'Not an array and not a file' => [
            ['min' => 1],
            'Whatever',
            false,
            Count::ERROR_NOT_ARRAY,
        ];

        yield 'Single file path' => [
            ['min' => 1],
            __DIR__ . '/_files/picture.jpg',
            true,
            null,
        ];

        yield 'Non-files in the list are ignored' => [
            ['min' => 2, 'max' => 2],
            [
                __DIR__ . '/_files/picture.jpg',
                __DIR__ . '/_files/test.zip',
                'Not a file',
            ],
            true,
            null,
        ];

        $phpUpload1 = [
            'tmp_name' => __DIR__ . '/_files/picture.jpg',
            'name'     => 'picture.jpg',
            'size'     => 200,
            'error'    => UPLOAD_ERR_OK,
            'type'     => 'text',
        ];

        $phpUpload2 = [
            'tmp_name' => __DIR__ . '/_files/test.zip',
            'name'     => 'test.zip',
            'size'     => 200,
            'error'    => UPLOAD_ERR_OK,
            'type'     => 'text',
        ];

        yield 'Single PHP upload array' => [
            ['min' => 1],
            $phpUpload1,
            true,
            null,
        ];

        yield 'Multiple PHP Uploads' => [
            ['min' => 1],
            [$phpUpload1, $phpUpload2],
            true,
            null,
        ];

        $phpUploadFailure = [
            'tmp_name' => null,
            'name'     => null,
            'size'     => 0,
            'error'    => UPLOAD_ERR_NO_FILE,
            'type'     => null,
        ];

        yield 'Failed upload is uncounted' => [
            ['min' => 3],
            [$phpUpload1, $phpUpload2, $phpUploadFailure],
            false,
            Count::TOO_FEW,
        ];

        $psrUpload1 = new UploadedFile(
            __DIR__ . '/_files/picture.jpg',
            200,
            UPLOAD_ERR_OK,
            'foo.jpg',
            'foo',
        );

        $psrUpload2 = new UploadedFile(
            __DIR__ . '/_files/test.zip',
            200,
            UPLOAD_ERR_OK,
            'foo.zip',
            'foo',
        );

        yield 'Single PSR upload array' => [
            ['min' => 1],
            $psrUpload1,
            true,
            null,
        ];

        yield 'Multiple PSR Uploads' => [
            ['min' => 1],
            [$psrUpload1, $psrUpload2],
            true,
            null,
        ];
    }

    /**
     * Ensures that the validator follows expected behavior
     *
     * @param OptionsArgument $options
     */
    #[DataProvider('basicDataProvider')]
    public function testBasic(array $options, mixed $value, bool $expect, string|null $errorKey): void
    {
        $validator = new Count($options);

        self::assertSame($expect, $validator->isValid($value));

        if ($errorKey !== null) {
            self::assertArrayHasKey($errorKey, $validator->getMessages());
        }
    }
}
