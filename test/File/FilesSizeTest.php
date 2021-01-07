<?php

/**
 * @see       https://github.com/laminas/laminas-validator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-validator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-validator/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Validator\File;

use Laminas\Validator\Exception\InvalidArgumentException;
use Laminas\Validator\File;
use PHPUnit\Framework\TestCase;

/**
 * @group      Laminas_Validator
 */
class FilesSizeTest extends TestCase
{
    /** @var bool */
    public $multipleOptionsDetected;

    protected function setUp() : void
    {
        $this->multipleOptionsDetected = false;
    }

    /**
     * Ensures that the validator follows expected behavior
     *
     * @dataProvider basicDataProvider
     * @param array|int $options
     * @return void
     */
    public function testBasic($options, bool $expected1, bool $expected2, bool $expected3)
    {
        $validator = new File\FilesSize(...$options);
        $this->assertSame(
            $expected1,
            $validator->isValid(__DIR__ . '/_files/testsize.mo')
        );
        $this->assertSame(
            $expected2,
            $validator->isValid(__DIR__ . '/_files/testsize2.mo')
        );
        $this->assertSame(
            $expected3,
            $validator->isValid(__DIR__ . '/_files/testsize3.mo')
        );
    }

    /**
     * @psalm-return array<string, array{
     *     0: int[]|array<string, int|string>,
     *     1: bool,
     *     2: bool,
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
        $validator = new File\FilesSize(['min' => 0, 'max' => 500000]);
        $this->assertEquals(true, $validator->isValid([
            __DIR__ . '/_files/testsize.mo',
            __DIR__ . '/_files/testsize.mo',
            __DIR__ . '/_files/testsize2.mo',
        ]));
    }

    public function testFileDoNotExist(): void
    {
        $validator = new File\FilesSize(['min' => 0, 'max' => 200]);
        $this->assertEquals(false, $validator->isValid(__DIR__ . '/_files/nofile.mo'));
        $this->assertArrayHasKey('fileFilesSizeNotReadable', $validator->getMessages());
    }

    /**
     * Ensures that getMin() returns expected value
     *
     * @return void
     */
    public function testGetMin()
    {
        $validator = new File\FilesSize(['min' => 1, 'max' => 100]);
        $this->assertEquals('1B', $validator->getMin());

        $validator = new File\FilesSize(['min' => 1, 'max' => 100]);
        $this->assertEquals('1B', $validator->getMin());

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('greater than or equal');
        $validator = new File\FilesSize(['min' => 100, 'max' => 1]);
    }

    /**
     * Ensures that setMin() returns expected value
     *
     * @return void
     */
    public function testSetMin()
    {
        $validator = new File\FilesSize(['min' => 1000, 'max' => 10000]);
        $validator->setMin(100);
        $this->assertEquals('100B', $validator->getMin());

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('less than or equal');
        $validator->setMin(20000);
    }

    /**
     * Ensures that getMax() returns expected value
     *
     * @return void
     */
    public function testGetMax()
    {
        $validator = new File\FilesSize(['min' => 1, 'max' => 100]);
        $this->assertEquals('100B', $validator->getMax());

        $validator = new File\FilesSize(['min' => 1, 'max' => 100000]);
        $this->assertEquals('97.66kB', $validator->getMax());

        $validator = new File\FilesSize(2000);
        $validator->useByteString(false);
        $test = $validator->getMax();
        $this->assertEquals('2000', $test);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('greater than or equal');
        $validator = new File\FilesSize(['min' => 100, 'max' => 1]);
    }

    /**
     * Ensures that setMax() returns expected value
     *
     * @return void
     */
    public function testSetMax()
    {
        $validator = new File\FilesSize(['min' => 1000, 'max' => 10000]);
        $validator->setMax(1000000);
        $this->assertEquals('976.56kB', $validator->getMax());

        $validator->setMin(100);
        $this->assertEquals('976.56kB', $validator->getMax());
    }

    public function testConstructorShouldRaiseErrorWhenPassedMultipleOptions(): void
    {
        $handler = set_error_handler([$this, 'errorHandler'], E_USER_NOTICE);
        $validator = new File\FilesSize(1000, 10000);
        restore_error_handler();
    }

    /**
     * Ensures that the validator returns size infos
     *
     * @return void
     */
    public function testFailureMessage()
    {
        $validator = new File\FilesSize(['min' => 9999, 'max' => 10000]);
        $this->assertFalse($validator->isValid([
            __DIR__ . '/_files/testsize.mo',
            __DIR__ . '/_files/testsize.mo',
            __DIR__ . '/_files/testsize2.mo',
        ]));
        $messages = $validator->getMessages();
        $this->assertStringContainsString('9.76kB', current($messages));
        $this->assertStringContainsString('1.55kB', current($messages));

        $validator = new File\FilesSize(['min' => 9999, 'max' => 10000, 'useByteString' => false]);
        $this->assertFalse($validator->isValid([
            __DIR__ . '/_files/testsize.mo',
            __DIR__ . '/_files/testsize.mo',
            __DIR__ . '/_files/testsize2.mo',
        ]));
        $messages = $validator->getMessages();
        $this->assertStringContainsString('9999', current($messages));
        $this->assertStringContainsString('1588', current($messages));
    }

    public function errorHandler($errno, $errstr): void
    {
        if (strstr($errstr, 'deprecated')) {
            $this->multipleOptionsDetected = true;
        }
    }

    public function testEmptyFileShouldReturnFalseAndDisplayNotFoundMessage(): void
    {
        $validator = new File\FilesSize(0);

        $this->assertFalse($validator->isValid(''));
        $this->assertArrayHasKey(File\FilesSize::NOT_READABLE, $validator->getMessages());

        $filesArray = [
            'name'      => '',
            'size'      => 0,
            'tmp_name'  => '',
            'error'     => UPLOAD_ERR_NO_FILE,
            'type'      => '',
        ];

        $this->assertFalse($validator->isValid($filesArray));
        $this->assertArrayHasKey(File\FilesSize::NOT_READABLE, $validator->getMessages());
    }

    public function testFilesFormat(): void
    {
        $validator = new File\FilesSize(['min' => 0, 'max' => 2000]);

        $this->assertTrue(
            $validator->isValid($this->createFileInfo(__DIR__ . '/_files/testsize.mo'))
        );
        $this->assertTrue(
            $validator->isValid($this->createFileInfo(__DIR__ . '/_files/testsize2.mo'))
        );
        $this->assertFalse(
            $validator->isValid($this->createFileInfo(__DIR__ . '/_files/testsize3.mo'))
        );

        $validator = new File\FilesSize(['min' => 0, 'max' => 500000]);

        $this->assertTrue($validator->isValid([
            $this->createFileInfo(__DIR__ . '/_files/testsize.mo'),
            $this->createFileInfo(__DIR__ . '/_files/testsize.mo'),
            $this->createFileInfo(__DIR__ . '/_files/testsize2.mo'),
        ]));
    }

    public function testIllegalFilesFormat(): void
    {
        $validator = new File\FilesSize(['min' => 0, 'max' => 2000]);
        $this->expectException(InvalidArgumentException::class);
        $validator->isValid([
            [
                'error' => 0,
            ],
        ]);
    }

    /**
     * @psalm-return array<string, mixed>
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

        $validator = new File\FilesSize($min, $max, $useBytesAsString);

        $this->assertEquals($min, $validator->getMin(true));
        $this->assertEquals($max, $validator->getMax(true));
        $this->assertSame($useBytesAsString, $validator->getByteString());
    }

    public function testIsValidRaisesExceptionForArrayValueNotInFilesFormat(): void
    {
        $validator = new File\FilesSize(['min' => 0, 'max' => 2000]);
        $value     = [['foo' => 'bar']];
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Value array must be in $_FILES format');
        $validator->isValid($value);
    }
}
