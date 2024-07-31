<?php

declare(strict_types=1);

namespace LaminasTest\Validator\File;

use Laminas\Validator\Exception\InvalidArgumentException;
use Laminas\Validator\File\FilesSize;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use function basename;
use function chmod;
use function current;
use function filesize;
use function json_encode;
use function touch;
use function unlink;

use const UPLOAD_ERR_NO_FILE;

/** @psalm-import-type OptionsArgument from FilesSize */
final class FilesSizeTest extends TestCase
{
    /**
     * Ensures that the validator follows expected behavior
     *
     * @param OptionsArgument $options
     */
    #[DataProvider('basicDataProvider')]
    public function testBasic(array $options, bool $expected1, bool $expected2, bool $expected3): void
    {
        $validator = new FilesSize($options);

        self::assertSame(
            $expected1,
            $validator->isValid(__DIR__ . '/_files/testsize.mo'),
            json_encode($validator->getMessages()),
        );
        self::assertSame(
            $expected2,
            $validator->isValid(__DIR__ . '/_files/testsize2.mo'),
            json_encode($validator->getMessages()),
        );
        self::assertSame(
            $expected3,
            $validator->isValid(__DIR__ . '/_files/picture.jpg'),
            json_encode($validator->getMessages()),
        );
    }

    /**
     * @psalm-return array<string, array{
     *     0: OptionsArgument,
     *     1: bool,
     *     2: bool,
     *     3: bool,
     * }>
     */
    public static function basicDataProvider(): array
    {
        return [
            // phpcs:disable
            'minimum: 0 byte; maximum: 500 bytes; integer'  => [['max' => 500],                 false, false, false],
            'minimum: 0 byte; maximum: 500 bytes; array'    => [['min' => 0, 'max' => 500],     false, false, false],
            'minimum: 0 byte; maximum: 2000 bytes; integer' => [['max' => 2000],                true,  true,  false],
            'minimum: 0 byte; maximum: 2000 bytes; array'   => [['min' => 0, 'max' => 2000],    true,  true,  false],
            'minimum: 0 byte; maximum: 500 kilobytes'       => [['min' => 0, 'max' => 500000],  true,  true,  true],
            'minimum: 0 byte; maximum: 2 megabytes; 2 MB'   => [['min' => 0, 'max' => '2 MB'],  true,  true,  true],
            'minimum: 0 byte; maximum: 2 megabytes; 2MB'    => [['min' => 0, 'max' => '2MB'],   true,  true,  true],
            'minimum: 0 byte; maximum: 2 megabytes; 2  MB'  => [['min' => 0, 'max' => '2  MB'], true,  true,  true],
            'minimum: 1k; maximum: 2 megabytes; 2  MB'      => [['min' => '1k', 'max' => '2  MB'], true,  true,  true],
            // phpcs:enable
        ];
    }

    public function testMultipleFiles(): void
    {
        $validator = new FilesSize(['min' => 0, 'max' => 500000]);

        self::assertTrue($validator->isValid([
            __DIR__ . '/_files/testsize.mo',
            __DIR__ . '/_files/testsize.mo',
            __DIR__ . '/_files/testsize2.mo',
        ]));
    }

    public function testFileDoNotExist(): void
    {
        $validator = new FilesSize(['min' => 0, 'max' => 200]);

        self::assertFalse($validator->isValid(__DIR__ . '/_files/nofile.mo'));
        self::assertArrayHasKey(FilesSize::NOT_READABLE, $validator->getMessages());
    }

    /**
     * Ensures that the validator returns size infos
     */
    public function testFailureMessage(): void
    {
        $validator = new FilesSize(['min' => 9999, 'max' => 10000]);

        self::assertFalse($validator->isValid([
            __DIR__ . '/_files/testsize.mo',
            __DIR__ . '/_files/testsize.mo', // Duplicate Path
            __DIR__ . '/_files/testsize2.mo',
        ]));

        $messages = $validator->getMessages();

        self::assertStringContainsString('9.76kB', current($messages));
        self::assertStringContainsString('1.55kB', current($messages));

        $validator = new FilesSize(['min' => 9999, 'max' => 10000, 'useByteString' => false]);

        self::assertFalse($validator->isValid([
            __DIR__ . '/_files/testsize.mo',
            __DIR__ . '/_files/testsize.mo', // Duplicate Path
            __DIR__ . '/_files/testsize2.mo',
        ]));

        $messages = $validator->getMessages();

        self::assertStringContainsString('9999', current($messages));
        self::assertStringContainsString('1588', current($messages));
    }

    public function testEmptyFileShouldReturnFalseAndDisplayNotFoundMessage(): void
    {
        $validator = new FilesSize(['min' => 0]);

        self::assertFalse($validator->isValid(''));
        self::assertArrayHasKey(FilesSize::NOT_READABLE, $validator->getMessages());

        $filesArray = [
            'name'     => '',
            'size'     => 0,
            'tmp_name' => '',
            'error'    => UPLOAD_ERR_NO_FILE,
            'type'     => '',
        ];

        self::assertFalse($validator->isValid($filesArray));
        self::assertArrayHasKey(FilesSize::NOT_READABLE, $validator->getMessages());
    }

    public function testFilesFormat(): void
    {
        $validator = new FilesSize(['min' => 0, 'max' => 2000]);

        self::assertTrue(
            $validator->isValid($this->createFileInfo(__DIR__ . '/_files/testsize.mo'))
        );
        self::assertTrue(
            $validator->isValid($this->createFileInfo(__DIR__ . '/_files/testsize2.mo'))
        );
        self::assertFalse(
            $validator->isValid($this->createFileInfo(__DIR__ . '/_files/picture.jpg'))
        );

        $validator = new FilesSize(['min' => 0, 'max' => '2kb']);

        self::assertTrue($validator->isValid([
            $this->createFileInfo(__DIR__ . '/_files/testsize.mo'),
            $this->createFileInfo(__DIR__ . '/_files/testsize.mo'),
            $this->createFileInfo(__DIR__ . '/_files/testsize2.mo'),
        ]));
    }

    public function testIllegalFilesFormat(): void
    {
        $validator = new FilesSize(['min' => 0, 'max' => 2000]);

        self::assertFalse($validator->isValid([['error' => 0]]));
        self::assertArrayHasKey(FilesSize::NOT_READABLE, $validator->getMessages());
    }

    /**
     * @psalm-return array<string, scalar>
     */
    private function createFileInfo(string $file): array
    {
        return [
            'tmp_name' => $file,
            'name'     => basename($file),
            'error'    => 0,
            'type'     => '',
            'size'     => filesize($file),
        ];
    }

    public function testMinOrMaxMustBeSet(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('One of `min` or `max` options are required');
        new FilesSize([]);
    }

    public function testMinMustBeLessThanMax(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The `min` option cannot exceed the `max` option');
        new FilesSize(['min' => 500, 'max' => 100]);
    }

    public function testUnreadableFile(): void
    {
        $validator = new FilesSize(['min' => 0, 'max' => '10MB']);

        $path = __DIR__ . '/_files/no-read.txt';
        touch($path);
        chmod($path, 0333);
        try {
            self::assertFalse($validator->isValid($path));
            $messages = $validator->getMessages();
            self::assertArrayHasKey(FilesSize::NOT_READABLE, $messages);
        } finally {
            unlink($path);
        }
    }
}
