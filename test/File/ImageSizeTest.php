<?php

declare(strict_types=1);

namespace LaminasTest\Validator\File;

use Laminas\Diactoros\UploadedFile;
use Laminas\Validator\Exception\InvalidArgumentException;
use Laminas\Validator\File\ImageSize;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

use function array_merge;
use function basename;
use function chmod;
use function current;
use function file_exists;
use function touch;
use function unlink;

use const UPLOAD_ERR_NO_FILE;
use const UPLOAD_ERR_OK;

/** @psalm-import-type OptionsArgument from ImageSize */
final class ImageSizeTest extends TestCase
{
    /**
     * @psalm-return array<array-key, array{
     *     0: OptionsArgument,
     *     1: mixed,
     *     2: bool,
     *     3: null|list<string>
     * }>
     */
    public static function basicBehaviorDataProvider(): array
    {
        $testFile     = __DIR__ . '/_files/picture.jpg';
        $pictureTests = [
            [
                ['minWidth' => 0,   'minHeight' => 10,  'maxWidth' => 1000, 'maxHeight' => 2000],
                $testFile,
                true,
                null,
            ],
            [
                ['minWidth' => 0,   'minHeight' => 0,   'maxWidth' => 200,  'maxHeight' => 200],
                $testFile,
                true,
                null,
            ],
            [
                ['minWidth' => 150, 'minHeight' => 150, 'maxWidth' => 200,  'maxHeight' => 200],
                $testFile,
                false,
                [ImageSize::WIDTH_TOO_SMALL, ImageSize::HEIGHT_TOO_SMALL],
            ],
            [
                ['minWidth' => 80,  'minHeight' => 0,   'maxWidth' => 80,   'maxHeight' => 200],
                $testFile,
                true,
                null,
            ],
            [
                ['minWidth' => 0,   'minHeight' => 0,   'maxWidth' => 60,   'maxHeight' => 200],
                $testFile,
                false,
                [ImageSize::WIDTH_TOO_BIG],
            ],
            [
                ['minWidth' => 90,  'minHeight' => 0,   'maxWidth' => 200,  'maxHeight' => 200],
                $testFile,
                false,
                [ImageSize::WIDTH_TOO_SMALL],
            ],
            [
                ['minWidth' => 0,   'minHeight' => 0,   'maxWidth' => 200,  'maxHeight' => 80],
                $testFile,
                false,
                [ImageSize::HEIGHT_TOO_BIG],
            ],
            [
                ['minWidth' => 0,   'minHeight' => 110, 'maxWidth' => 200,  'maxHeight' => 140],
                $testFile,
                false,
                [ImageSize::HEIGHT_TOO_SMALL],
            ],
        ];

        $testFile    = __DIR__ . '/_files/nofile.mo';
        $noFileTests = [
            [
                ['minWidth' => 0, 'minHeight' => 10, 'maxWidth' => 1000, 'maxHeight' => 2000],
                $testFile,
                false,
                [ImageSize::NOT_READABLE],
            ],
        ];

        $testFile    = __DIR__ . '/_files/badpicture.jpg';
        $badPicTests = [
            [
                ['minWidth' => 0, 'minHeight' => 10, 'maxWidth' => 1000, 'maxHeight' => 2000],
                $testFile,
                false,
                [ImageSize::NOT_DETECTED],
            ],
        ];

        // Dupe data in File Upload format
        $testData = array_merge($pictureTests, $noFileTests, $badPicTests);
        foreach ($testData as $data) {
            $fileUpload = [
                'tmp_name' => $data[1],
                'name'     => basename($data[1]),
                'size'     => 200,
                'error'    => UPLOAD_ERR_OK,
                'type'     => 'text',
            ];
            $testData[] = [$data[0], $fileUpload, $data[2], $data[3]];

            if (! file_exists($data[1])) {
                continue; // Cannot use a non-existent file as a stream argument
            }

            $psrUpload = new UploadedFile(
                $data[1],
                200,
                UPLOAD_ERR_OK,
                basename($data[1]),
                'image/jpg',
            );

            $testData[] = [$data[0], $psrUpload, $data[2], $data[3]];
        }

        return $testData;
    }

    /**
     * Ensures that the validator follows expected behavior
     *
     * @param OptionsArgument $options
     * @param list<string>|null $messageKeys
     */
    #[DataProvider('basicBehaviorDataProvider')]
    public function testBasic(array $options, mixed $isValidParam, bool $expected, array|null $messageKeys): void
    {
        $validator = new ImageSize($options);

        self::assertSame($expected, $validator->isValid($isValidParam));

        if ($messageKeys === null) {
            self::assertSame([], $validator->getMessages());

            return;
        }

        foreach ($messageKeys as $messageKey) {
            self::assertArrayHasKey($messageKey, $validator->getMessages());
        }
    }

    #[Group('Laminas-11258')]
    public function testLaminas11258(): void
    {
        $validator = new ImageSize([
            'minWidth'  => 100,
            'minHeight' => 1000,
            'maxWidth'  => 10000,
            'maxHeight' => 100000,
        ]);

        self::assertFalse($validator->isValid(__DIR__ . '/_files/nofile.mo'));
        self::assertArrayHasKey(ImageSize::NOT_READABLE, $validator->getMessages());
        self::assertStringContainsString('does not exist', current($validator->getMessages()));
    }

    public function testEmptyFileShouldReturnFalseAndDisplayNotFoundMessage(): void
    {
        $validator = new ImageSize(['maxWidth' => 100]);

        self::assertFalse($validator->isValid(''));
        self::assertArrayHasKey(ImageSize::NOT_READABLE, $validator->getMessages());

        $filesArray = [
            'name'     => '',
            'size'     => 0,
            'tmp_name' => '',
            'error'    => UPLOAD_ERR_NO_FILE,
            'type'     => '',
        ];

        self::assertFalse($validator->isValid($filesArray));
        self::assertArrayHasKey(ImageSize::NOT_READABLE, $validator->getMessages());
    }

    public function testThatOptionsMustBeProvided(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('At least one size constraint is required');

        new ImageSize([]);
    }

    public function testInvalidWidthConstraints(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Max width or height must exceed the minimum equivalent');

        new ImageSize(['minWidth' => 100, 'maxWidth' => 50]);
    }

    public function testInvalidHeightConstraints(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Max width or height must exceed the minimum equivalent');

        new ImageSize(['minHeight' => 100, 'maxHeight' => 50]);
    }

    public function testUnreadableFile(): void
    {
        $validator = new ImageSize(['minWidth' => 0, 'maxWidth' => 500]);

        $path = __DIR__ . '/_files/no-read.txt';
        touch($path);
        chmod($path, 0333);
        try {
            self::assertFalse($validator->isValid($path));
            $messages = $validator->getMessages();
            self::assertArrayHasKey(ImageSize::NOT_READABLE, $messages);
        } finally {
            unlink($path);
        }
    }
}
