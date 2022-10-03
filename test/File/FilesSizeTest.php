<?php

declare(strict_types=1);

namespace LaminasTest\Validator\File;

use Laminas\Validator\Exception\InvalidArgumentException;
use Laminas\Validator\File\FilesSize;
use PHPUnit\Framework\TestCase;

use function basename;
use function current;
use function filesize;
use function restore_error_handler;
use function set_error_handler;
use function strstr;

use const E_USER_NOTICE;
use const UPLOAD_ERR_NO_FILE;

/**
 * @group Laminas_Validator
 * @covers \Laminas\Validator\File\FilesSize
 */
final class FilesSizeTest extends TestCase
{
    public bool $multipleOptionsDetected;

    protected function setUp(): void
    {
        parent::setUp();

        $this->multipleOptionsDetected = false;
    }

    /**
     * Ensures that the validator follows expected behavior
     *
     * @dataProvider basicDataProvider
     * @param array|int $options
     */
    public function testBasic($options, bool $expected1, bool $expected2, bool $expected3): void
    {
        $validator = new FilesSize(...$options);

        self::assertSame(
            $expected1,
            $validator->isValid(__DIR__ . '/_files/testsize.mo')
        );
        self::assertSame(
            $expected2,
            $validator->isValid(__DIR__ . '/_files/testsize2.mo')
        );
        self::assertSame(
            $expected3,
            $validator->isValid(__DIR__ . '/_files/testsize3.mo')
        );
    }

    /**
     * @psalm-return array<string, array{
     *     0: array,
     *     1: bool
     *     2: bool
     *     3: bool
     * }>
     */
    public function basicDataProvider(): array
    {
        return [
            // phpcs:disable
            'minimum: 0 byte; maximum: 500 bytes; integer'  => [[500],                            false, false, false],
            'minimum: 0 byte; maximum: 500 bytes; array'    => [[['min' => 0, 'max' => 500]],     false, false, false],
            'minimum: 0 byte; maximum: 2000 bytes; integer' => [[2000],                           true,  true,  false],
            'minimum: 0 byte; maximum: 2000 bytes; array'   => [[['min' => 0, 'max' => 2000]],    true,  true,  false],
            'minimum: 0 byte; maximum: 500 kilobytes'       => [[['min' => 0, 'max' => 500000]],  true,  true,  true],
            'minimum: 0 byte; maximum: 2 megabytes; 2 MB'   => [[['min' => 0, 'max' => '2 MB']],  true,  true,  true],
            'minimum: 0 byte; maximum: 2 megabytes; 2MB'    => [[['min' => 0, 'max' => '2MB']],   true,  true,  true],
            'minimum: 0 byte; maximum: 2 megabytes; 2  MB'  => [[['min' => 0, 'max' => '2  MB']], true,  true,  true],
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
        self::assertArrayHasKey('fileFilesSizeNotReadable', $validator->getMessages());
    }

    /**
     * Ensures that getMin() returns expected value
     */
    public function testGetMin(): void
    {
        $validator = new FilesSize(['min' => 1, 'max' => 100]);

        self::assertSame('1B', $validator->getMin());

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('greater than or equal');

        new FilesSize(['min' => 100, 'max' => 1]);
    }

    /**
     * Ensures that setMin() returns expected value
     */
    public function testSetMin(): void
    {
        $validator = new FilesSize(['min' => 1000, 'max' => 10000]);
        $validator->setMin(100);

        self::assertSame('100B', $validator->getMin());

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('less than or equal');

        $validator->setMin(20000);
    }

    /**
     * Ensures that getMax() returns expected value
     */
    public function testGetMax(): void
    {
        $validator = new FilesSize(['min' => 1, 'max' => 100]);

        self::assertSame('100B', $validator->getMax());

        $validator = new FilesSize(['min' => 1, 'max' => 100000]);

        self::assertSame('97.66kB', $validator->getMax());

        $validator = new FilesSize(2000);
        $validator->useByteString(false);
        $test = $validator->getMax();

        self::assertSame(2000, $test);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('greater than or equal');

        new FilesSize(['min' => 100, 'max' => 1]);
    }

    /**
     * Ensures that setMax() returns expected value
     */
    public function testSetMax(): void
    {
        $validator = new FilesSize(['min' => 1000, 'max' => 10000]);
        $validator->setMax(1_000_000);

        self::assertSame('976.56kB', $validator->getMax());

        $validator->setMin(100);

        self::assertSame('976.56kB', $validator->getMax());
    }

    public function testConstructorShouldRaiseErrorWhenPassedMultipleOptions(): void
    {
        set_error_handler([$this, 'errorHandler'], E_USER_NOTICE);
        new FilesSize(1000, 10000);
        restore_error_handler();
    }

    /**
     * Ensures that the validator returns size infos
     */
    public function testFailureMessage(): void
    {
        $validator = new FilesSize(['min' => 9999, 'max' => 10000]);

        self::assertFalse($validator->isValid([
            __DIR__ . '/_files/testsize.mo',
            __DIR__ . '/_files/testsize.mo',
            __DIR__ . '/_files/testsize2.mo',
        ]));

        $messages = $validator->getMessages();

        self::assertStringContainsString('9.76kB', current($messages));
        self::assertStringContainsString('1.55kB', current($messages));

        $validator = new FilesSize(['min' => 9999, 'max' => 10000, 'useByteString' => false]);

        self::assertFalse($validator->isValid([
            __DIR__ . '/_files/testsize.mo',
            __DIR__ . '/_files/testsize.mo',
            __DIR__ . '/_files/testsize2.mo',
        ]));

        $messages = $validator->getMessages();

        self::assertStringContainsString('9999', current($messages));
        self::assertStringContainsString('1588', current($messages));
    }

    public function errorHandler(int $errno, string $errstr): void
    {
        if (strstr($errstr, 'deprecated')) {
            $this->multipleOptionsDetected = true;
        }
    }

    public function testEmptyFileShouldReturnFalseAndDisplayNotFoundMessage(): void
    {
        $validator = new FilesSize(0);

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
            $validator->isValid($this->createFileInfo(__DIR__ . '/_files/testsize3.mo'))
        );

        $validator = new FilesSize(['min' => 0, 'max' => 500000]);

        self::assertTrue($validator->isValid([
            $this->createFileInfo(__DIR__ . '/_files/testsize.mo'),
            $this->createFileInfo(__DIR__ . '/_files/testsize.mo'),
            $this->createFileInfo(__DIR__ . '/_files/testsize2.mo'),
        ]));
    }

    public function testIllegalFilesFormat(): void
    {
        $validator = new FilesSize(['min' => 0, 'max' => 2000]);

        $this->expectException(InvalidArgumentException::class);

        $validator->isValid([['error' => 0]]);
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

    public function testConstructorCanAcceptAllOptionsAsDiscreteArguments(): void
    {
        $min              = 0;
        $max              = 10;
        $useBytesAsString = false;

        $validator = new FilesSize($min, $max, $useBytesAsString);

        self::assertNull($validator->getMin(true));
        self::assertSame($max, $validator->getMax(true));
        self::assertSame($useBytesAsString, $validator->getByteString());
    }

    public function testIsValidRaisesExceptionForArrayValueNotInFilesFormat(): void
    {
        $validator = new FilesSize(['min' => 0, 'max' => 2000]);
        $value     = [['foo' => 'bar']];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Value array must be in $_FILES format');

        $validator->isValid($value);
    }
}
