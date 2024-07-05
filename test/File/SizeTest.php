<?php

declare(strict_types=1);

namespace LaminasTest\Validator\File;

use Laminas\Diactoros\UploadedFile;
use Laminas\Validator\Exception\InvalidArgumentException;
use Laminas\Validator\File\Size;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

use function basename;
use function chmod;
use function filesize;
use function touch;
use function unlink;

use const UPLOAD_ERR_NO_FILE;
use const UPLOAD_ERR_OK;

/** @psalm-import-type OptionsArgument from Size */
final class SizeTest extends TestCase
{
    /**
     * @psalm-return array<array-key, array{
     *     0: OptionsArgument,
     *     1: mixed,
     *     2: bool
     * }>
     */
    public static function basicBehaviorDataProvider(): array
    {
        $testFile = __DIR__ . '/_files/testsize.mo';
        $testData = [
            //    Options, isValid Param, Expected value
            [['max' => 794], $testFile, true],
            [['max' => 500], $testFile, false],
            [['min' => 0, 'max' => 10000], $testFile, true],
            [['min' => 0, 'max' => '10 MB'], $testFile, true],
            [['min' => '4B', 'max' => '10 MB'], $testFile, true],
            [['min' => 0, 'max' => '10MB'], $testFile, true],
            [['min' => 0, 'max' => '10  MB'], $testFile, true],
            [['min' => 794], $testFile, true],
            [['min' => 0, 'max' => 500], $testFile, false],
        ];

        // Dupe data in File Upload format
        foreach ($testData as $data) {
            $fileUpload = [
                'tmp_name' => $testFile,
                'name'     => basename($testFile),
                'size'     => filesize($testFile),
                'error'    => UPLOAD_ERR_OK,
                'type'     => 'text',
            ];
            $testData[] = [$data[0], $fileUpload, $data[2]];

            $psrUpload = new UploadedFile(
                $testFile,
                filesize($testFile),
                UPLOAD_ERR_OK,
                basename($testFile),
                'text/plain',
            );

            $testData[] = [$data[0], $psrUpload, $data[2]];
        }

        return $testData;
    }

    /**
     * Ensures that the validator follows expected behavior
     *
     * @param OptionsArgument $options
     */
    #[DataProvider('basicBehaviorDataProvider')]
    public function testBasic(array $options, mixed $value, bool $expected): void
    {
        $validator = new Size($options);

        self::assertSame($expected, $validator->isValid($value));
    }

    /**
     * Ensures that the validator returns size infos
     */
    public function testFailureMessage(): void
    {
        $validator = new Size(['min' => 9999, 'max' => 10000]);

        self::assertFalse($validator->isValid(__DIR__ . '/_files/testsize.mo'));

        $messages = $validator->getMessages();
        self::assertArrayHasKey(Size::TOO_SMALL, $messages);

        self::assertStringContainsString('9.76kB', $messages[Size::TOO_SMALL]);
        self::assertStringContainsString('794B', $messages[Size::TOO_SMALL]);

        $validator = new Size(['min' => 9999, 'max' => 10000, 'useByteString' => false]);

        self::assertFalse($validator->isValid(__DIR__ . '/_files/testsize.mo'));

        $messages = $validator->getMessages();
        self::assertArrayHasKey(Size::TOO_SMALL, $messages);

        self::assertStringContainsString('9999', $messages[Size::TOO_SMALL]);
        self::assertStringContainsString('794', $messages[Size::TOO_SMALL]);
    }

    #[Group('Laminas-11258')]
    public function testLaminas11258(): void
    {
        $validator = new Size(['min' => 1, 'max' => 10000]);

        self::assertFalse($validator->isValid(__DIR__ . '/_files/nofile.mo'));
        $messages = $validator->getMessages();
        self::assertArrayHasKey(Size::NOT_FOUND, $messages);
        self::assertStringContainsString('does not exist', $messages[Size::NOT_FOUND]);
    }

    public function testEmptyFileShouldReturnFalseAndDisplayNotFoundMessage(): void
    {
        $validator = new Size(['min' => 0]);

        self::assertFalse($validator->isValid(''));
        self::assertArrayHasKey(Size::NOT_FOUND, $validator->getMessages());

        $filesArray = [
            'name'     => '',
            'size'     => 0,
            'tmp_name' => '',
            'error'    => UPLOAD_ERR_NO_FILE,
            'type'     => '',
        ];

        self::assertFalse($validator->isValid($filesArray));
        $messages = $validator->getMessages();
        self::assertArrayHasKey(Size::NOT_FOUND, $messages);
    }

    public function testMinOrMaxMustBeSet(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('One of `min` or `max` options are required');
        new Size([]);
    }

    public function testMinMustBeLessThanMax(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The `min` option cannot exceed the `max` option');
        new Size(['min' => 500, 'max' => 100]);
    }

    public function testUnreadableFile(): void
    {
        $validator = new Size(['min' => 0, 'max' => '10MB']);

        $path = __DIR__ . '/_files/no-read.txt';
        touch($path);
        chmod($path, 0333);
        try {
            self::assertFalse($validator->isValid($path));
            $messages = $validator->getMessages();
            self::assertArrayHasKey(Size::NOT_FOUND, $messages);
        } finally {
            unlink($path);
        }
    }
}
