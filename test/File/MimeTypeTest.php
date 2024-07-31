<?php

declare(strict_types=1);

namespace LaminasTest\Validator\File;

use Laminas\Diactoros\UploadedFile;
use Laminas\Validator\Exception\InvalidArgumentException;
use Laminas\Validator\File\MimeType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

use function basename;
use function chmod;
use function current;
use function touch;
use function unlink;

use const UPLOAD_ERR_NO_FILE;
use const UPLOAD_ERR_OK;

/** @psalm-import-type OptionsArgument from MimeType */
final class MimeTypeTest extends TestCase
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
        $testFile  = __DIR__ . '/_files/picture.jpg';
        $fileArray = [
            'tmp_name' => $testFile,
            'name'     => basename($testFile),
            'size'     => 200,
            'error'    => UPLOAD_ERR_OK,
            'type'     => 'image/jpg',
        ];
        $upload    = new UploadedFile(
            $testFile,
            200,
            UPLOAD_ERR_OK,
            'Foo.jpg',
            'image/jpg',
        );

        return [
            //    Options, isValid Param, Expected value
            [['mimeType' => ['image/jpg', 'image/jpeg']], $fileArray, true],
            [['mimeType' => 'image'], $fileArray, true],
            [['mimeType' => 'test/notype'], $fileArray, false],
            [['mimeType' => 'image/gif, image/jpg, image/jpeg'], $fileArray, true],
            [['mimeType' => ['image/vasa', 'image/jpg', 'image/jpeg']], $fileArray, true],
            [['mimeType' => ['image/jpg', 'image/jpeg', 'gif']], $fileArray, true],
            [['mimeType' => ['image/gif', 'gif']], $fileArray, false],
            [['mimeType' => 'image/jp'], $fileArray, false],
            [['mimeType' => 'image/jpg2000'], $fileArray, false],
            [['mimeType' => 'image/jpeg2000'], $fileArray, false],
            [['mimeType' => 'image/jpeg'], $testFile, true],
            [['mimeType' => 'image/jpeg'], 'Not a file', false],
            [['mimeType' => 'image/jpeg'], __DIR__ . '/_files/test.zip', false],
            [['mimeType' => 'application/pdf'], __DIR__ . '/_files/crc32-int.pdf', true],
            [['mimeType' => 'application/pdf'], __DIR__ . '/_files/testsize.mo', false],
            [['mimeType' => 'application/pdf'], $upload, false],
            [['mimeType' => 'image/jpeg'], $upload, true],
        ];
    }

    /**
     * Ensures that the validator follows expected behavior
     *
     * @param OptionsArgument $options
     */
    #[DataProvider('basicBehaviorDataProvider')]
    public function testBasic($options, mixed $filesArray, bool $expected): void
    {
        $validator = new MimeType($options);
        self::assertSame($expected, $validator->isValid($filesArray));
    }

    #[Group('Laminas-11258')]
    public function testLaminas11258(): void
    {
        $validator = new MimeType([
            'mimeType' => [
                'image/gif',
                'image/jpg',
            ],
        ]);

        self::assertFalse($validator->isValid(__DIR__ . '/_files/nofile.mo'));
        self::assertArrayHasKey(MimeType::NOT_READABLE, $validator->getMessages());
        self::assertStringContainsString('does not exist', current($validator->getMessages()));
    }

    public function testEmptyFileShouldReturnFalseAndDisplayNotFoundMessage(): void
    {
        $validator = new MimeType(['mimeType' => 'image/gif']);

        self::assertFalse($validator->isValid(''));

        $filesArray = [
            'name'     => '',
            'size'     => 0,
            'tmp_name' => '',
            'error'    => UPLOAD_ERR_NO_FILE,
            'type'     => '',
        ];

        self::assertFalse($validator->isValid($filesArray));
    }

    public function testMissingMimeTypeOptionIsExceptional(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The `mimeType` option is required');

        new MimeType([]);
    }

    public function testUnreadableFile(): void
    {
        $validator = new MimeType(['mimeType' => 'text/plain']);

        $path = __DIR__ . '/_files/no-read.txt';
        touch($path);
        chmod($path, 0333);
        try {
            self::assertFalse($validator->isValid($path));
            $messages = $validator->getMessages();
            self::assertArrayHasKey(MimeType::NOT_READABLE, $messages);
        } finally {
            unlink($path);
        }
    }
}
