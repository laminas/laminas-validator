<?php

declare(strict_types=1);

namespace LaminasTest\Validator\File;

use Laminas\Validator\Exception\InvalidArgumentException;
use Laminas\Validator\File\ImageSize;
use PHPUnit\Framework\TestCase;

use function array_merge;
use function basename;
use function current;
use function is_array;

use const UPLOAD_ERR_NO_FILE;

/**
 * @group Laminas_Validator
 * @covers \Laminas\Validator\File\ImageSize
 */
final class ImageSizeTest extends TestCase
{
    /**
     * @psalm-return array<array-key, array{
     *     0: array<string, int>,
     *     1: string|array{
     *         tmp_name: string,
     *         name: string,
     *         size: int,
     *         error: int,
     *         type: string
     *     },
     *     2: bool,
     *     3: string|string[]
     * }>
     */
    public function basicBehaviorDataProvider(): array
    {
        $testFile     = __DIR__ . '/_files/picture.jpg';
        $pictureTests = [
            //    Options, isValid Param, Expected value, Expected message
            [
                ['minWidth' => 0,   'minHeight' => 10,  'maxWidth' => 1000, 'maxHeight' => 2000],
                $testFile,
                true,
                '',
            ],
            [
                ['minWidth' => 0,   'minHeight' => 0,   'maxWidth' => 200,  'maxHeight' => 200],
                $testFile,
                true,
                '',
            ],
            [
                ['minWidth' => 150, 'minHeight' => 150, 'maxWidth' => 200,  'maxHeight' => 200],
                $testFile,
                false,
                ['fileImageSizeWidthTooSmall', 'fileImageSizeHeightTooSmall'],
            ],
            [
                ['minWidth' => 80,  'minHeight' => 0,   'maxWidth' => 80,   'maxHeight' => 200],
                $testFile,
                true,
                '',
            ],
            [
                ['minWidth' => 0,   'minHeight' => 0,   'maxWidth' => 60,   'maxHeight' => 200],
                $testFile,
                false,
                'fileImageSizeWidthTooBig',
            ],
            [
                ['minWidth' => 90,  'minHeight' => 0,   'maxWidth' => 200,  'maxHeight' => 200],
                $testFile,
                false,
                'fileImageSizeWidthTooSmall',
            ],
            [
                ['minWidth' => 0,   'minHeight' => 0,   'maxWidth' => 200,  'maxHeight' => 80],
                $testFile,
                false,
                'fileImageSizeHeightTooBig',
            ],
            [
                ['minWidth' => 0,   'minHeight' => 110, 'maxWidth' => 200,  'maxHeight' => 140],
                $testFile,
                false,
                'fileImageSizeHeightTooSmall',
            ],
        ];

        $testFile    = __DIR__ . '/_files/nofile.mo';
        $noFileTests = [
            //    Options, isValid Param, Expected value, message
            [
                ['minWidth' => 0, 'minHeight' => 10, 'maxWidth' => 1000, 'maxHeight' => 2000],
                $testFile,
                false,
                'fileImageSizeNotReadable',
            ],
        ];

        $testFile    = __DIR__ . '/_files/badpicture.jpg';
        $badPicTests = [
            //    Options, isValid Param, Expected value, message
            [
                ['minWidth' => 0, 'minHeight' => 10, 'maxWidth' => 1000, 'maxHeight' => 2000],
                $testFile,
                false,
                'fileImageSizeNotDetected',
            ],
        ];

        // Dupe data in File Upload format
        $testData = array_merge($pictureTests, $noFileTests, $badPicTests);
        foreach ($testData as $data) {
            $fileUpload = [
                'tmp_name' => $data[1],
                'name'     => basename($data[1]),
                'size'     => 200,
                'error'    => 0,
                'type'     => 'text',
            ];
            $testData[] = [$data[0], $fileUpload, $data[2], $data[3]];
        }

        return $testData;
    }

    /**
     * Ensures that the validator follows expected behavior
     *
     * @dataProvider basicBehaviorDataProvider
     * @param string|array $isValidParam
     * @param string|string[] $messageKeys
     */
    public function testBasic(array $options, $isValidParam, bool $expected, $messageKeys): void
    {
        $validator = new ImageSize($options);

        self::assertSame($expected, $validator->isValid($isValidParam));

        if (! $expected) {
            if (! is_array($messageKeys)) {
                $messageKeys = [$messageKeys];
            }

            foreach ($messageKeys as $messageKey) {
                self::assertArrayHasKey($messageKey, $validator->getMessages());
            }
        }
    }

    /**
     * Ensures that the validator follows expected behavior for legacy Laminas\Transfer API
     *
     * @dataProvider basicBehaviorDataProvider
     * @param string|array $isValidParam
     * @param string|string[] $messageKeys
     */
    public function testLegacy(array $options, $isValidParam, bool $expected, $messageKeys): void
    {
        if (! is_array($isValidParam)) {
            self::markTestSkipped('An array is expected for legacy compat tests');
        }

        $validator = new ImageSize($options);

        self::assertSame($expected, $validator->isValid($isValidParam['tmp_name'], $isValidParam));

        if (! $expected) {
            if (! is_array($messageKeys)) {
                $messageKeys = [$messageKeys];
            }

            foreach ($messageKeys as $messageKey) {
                self::assertArrayHasKey($messageKey, $validator->getMessages());
            }
        }
    }

    /**
     * Ensures that getImageMin() returns expected value
     */
    public function testGetImageMin(): void
    {
        $validator = new ImageSize(['minWidth' => 1, 'minHeight' => 10, 'maxWidth' => 100, 'maxHeight' => 1000]);

        self::assertSame(['minWidth' => 1, 'minHeight' => 10], $validator->getImageMin());

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('greater than or equal');

        new ImageSize(['minWidth' => 1000, 'minHeight' => 100, 'maxWidth' => 10, 'maxHeight' => 1]);
    }

    /**
     * Ensures that setImageMin() returns expected value
     */
    public function testSetImageMin(): void
    {
        $validator = new ImageSize([
            'minWidth'  => 100,
            'minHeight' => 1000,
            'maxWidth'  => 10000,
            'maxHeight' => 100000,
        ]);
        $validator->setImageMin(['minWidth' => 10, 'minHeight' => 10]);

        self::assertSame(['minWidth' => 10, 'minHeight' => 10], $validator->getImageMin());

        $validator->setImageMin(['minWidth' => 9, 'minHeight' => 100]);

        self::assertSame(['minWidth' => 9, 'minHeight' => 100], $validator->getImageMin());

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('less than or equal');

        $validator->setImageMin(['minWidth' => 20000, 'minHeight' => 20000]);
    }

    /**
     * Ensures that getImageMax() returns expected value
     */
    public function testGetImageMax(): void
    {
        $validator = new ImageSize([
            'minWidth'  => 10,
            'minHeight' => 100,
            'maxWidth'  => 1000,
            'maxHeight' => 10000,
        ]);

        self::assertSame(['maxWidth' => 1000, 'maxHeight' => 10000], $validator->getImageMax());

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('greater than or equal');

        new ImageSize([
            'minWidth'  => 10000,
            'minHeight' => 1000,
            'maxWidth'  => 100,
            'maxHeight' => 10,
        ]);
    }

    /** @psalm-return array<array{array<string, int>, array<string, int>}> */
    public function imageMaxProvider(): array
    {
        return [
            [['maxWidth' => 100, 'maxHeight' => 100], ['maxWidth' => 100, 'maxHeight' => 100]],
            [['maxWidth' => 110, 'maxHeight' => 1000], ['maxWidth' => 110, 'maxHeight' => 1000]],
            [['maxHeight' => 1100], ['maxWidth' => 1000, 'maxHeight' => 1100]],
            [['maxWidth' => 120], ['maxWidth' => 120, 'maxHeight' => 10000]],
        ];
    }

    /**
     * Ensures that setImageMax() returns expected value
     *
     * @dataProvider imageMaxProvider
     * @param array<string, int> $imageMax
     * @param array<string, int> $expected
     */
    public function testSetImageMax(array $imageMax, array $expected): void
    {
        $validator = new ImageSize([
            'minWidth'  => 10,
            'minHeight' => 100,
            'maxWidth'  => 1000,
            'maxHeight' => 10000,
        ]);
        $validator->setImageMax($imageMax);

        self::assertSame($expected, $validator->getImageMax());

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('greater than or equal');

        $validator->setImageMax(['maxWidth' => 10000, 'maxHeight' => 1]);
    }

    /**
     * Ensures that getImageWidth() returns expected value
     */
    public function testGetImageWidth(): void
    {
        $validator = new ImageSize(['minWidth' => 1, 'minHeight' => 10, 'maxWidth' => 100, 'maxHeight' => 1000]);

        self::assertSame(['minWidth' => 1, 'maxWidth' => 100], $validator->getImageWidth());
    }

    /**
     * Ensures that setImageWidth() returns expected value
     */
    public function testSetImageWidth(): void
    {
        $validator = new ImageSize([
            'minWidth'  => 100,
            'minHeight' => 1000,
            'maxWidth'  => 10000,
            'maxHeight' => 100000,
        ]);
        $validator->setImageWidth(['minWidth' => 2000, 'maxWidth' => 2200]);

        self::assertSame(['minWidth' => 2000, 'maxWidth' => 2200], $validator->getImageWidth());

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('less than or equal');

        $validator->setImageWidth(['minWidth' => 20000, 'maxWidth' => 200]);
    }

    /**
     * Ensures that getImageHeight() returns expected value
     */
    public function testGetImageHeight(): void
    {
        $validator = new ImageSize(['minWidth' => 1, 'minHeight' => 10, 'maxWidth' => 100, 'maxHeight' => 1000]);

        self::assertSame(['minHeight' => 10, 'maxHeight' => 1000], $validator->getImageHeight());
    }

    /**
     * Ensures that setImageHeight() returns expected value
     */
    public function testSetImageHeight(): void
    {
        $validator = new ImageSize([
            'minWidth'  => 100,
            'minHeight' => 1000,
            'maxWidth'  => 10000,
            'maxHeight' => 100000,
        ]);
        $validator->setImageHeight(['minHeight' => 2000, 'maxHeight' => 2200]);

        self::assertSame(['minHeight' => 2000, 'maxHeight' => 2200], $validator->getImageHeight());

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('less than or equal');

        $validator->setImageHeight(['minHeight' => 20000, 'maxHeight' => 200]);
    }

    /**
     * @group Laminas-11258
     */
    public function testLaminas11258(): void
    {
        $validator = new ImageSize([
            'minWidth'  => 100,
            'minHeight' => 1000,
            'maxWidth'  => 10000,
            'maxHeight' => 100000,
        ]);

        self::assertFalse($validator->isValid(__DIR__ . '/_files/nofile.mo'));
        self::assertArrayHasKey('fileImageSizeNotReadable', $validator->getMessages());
        self::assertStringContainsString('does not exist', current($validator->getMessages()));
    }

    public function testEmptyFileShouldReturnFalseAndDisplayNotFoundMessage(): void
    {
        $validator = new ImageSize();

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

    public function testConstructorCanAcceptAllOptionsAsDiscreteArguments(): void
    {
        $minWidth  = 20;
        $minHeight = 10;
        $maxWidth  = 200;
        $maxHeight = 100;
        $validator = new ImageSize($minWidth, $minHeight, $maxWidth, $maxHeight);

        self::assertSame($minWidth, $validator->getMinWidth());
        self::assertSame($minHeight, $validator->getMinHeight());
        self::assertSame($maxWidth, $validator->getMaxWidth());
        self::assertSame($maxHeight, $validator->getMaxHeight());
    }

    public function testIsValidRaisesExceptionForArrayNotInFilesFormat(): void
    {
        $validator = new ImageSize([
            'minWidth'  => 100,
            'minHeight' => 1000,
            'maxWidth'  => 10000,
            'maxHeight' => 100000,
        ]);
        $value     = ['foo' => 'bar'];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Value array must be in $_FILES format');

        $validator->isValid($value);
    }
}
