<?php

declare(strict_types=1);

namespace LaminasTest\Validator\File;

use Laminas\Diactoros\UploadedFile;
use Laminas\Validator\Exception\InvalidArgumentException;
use Laminas\Validator\File\Hash;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use function basename;
use function chmod;
use function file_exists;
use function touch;
use function unlink;

use const UPLOAD_ERR_OK;

/** @psalm-import-type OptionsArgument from Hash */
final class HashTest extends TestCase
{
    /**
     * @psalm-return array<array-key, array{
     *     0: OptionsArgument,
     *     1: mixed,
     *     2: bool,
     *     3: string|null,
     * }>
     */
    public static function basicBehaviorDataProvider(): array
    {
        $testFile = __DIR__ . '/_files/picture.jpg';
        $testData = [
            //    Options, isValid Param, Expected value, Expected message
            [['hash' => '3f8d07e2'], $testFile, true, null],
            [['hash' => '9f8d07e2'], $testFile, false, Hash::DOES_NOT_MATCH],
            [['hash' => ['9f8d07e2', '3f8d07e2']], $testFile, true, null],
            [['hash' => ['9f8d07e2', '7f8d07e2']], $testFile, false, Hash::DOES_NOT_MATCH],
            [['hash' => 'whatever'], 'not-a-file.txt', false, Hash::NOT_FOUND],
            [
                ['hash' => 'b2a5334847b4328e7d19d9b41fd874dffa911c98', 'algorithm' => 'sha1'],
                $testFile,
                true,
                null,
            ],
            [
                ['hash' => '52a5334847b4328e7d19d9b41fd874dffa911c98', 'algorithm' => 'sha1'],
                $testFile,
                false,
                Hash::DOES_NOT_MATCH,
            ],
            [
                ['hash' => 'ed74c22109fe9f110579f77b053b8bc3', 'algorithm' => 'md5'],
                $testFile,
                true,
                null,
            ],
            [
                ['hash' => '4d74c22109fe9f110579f77b053b8bc3', 'algorithm' => 'md5'],
                $testFile,
                false,
                Hash::DOES_NOT_MATCH,
            ],
            [
                [
                    'hash'      => ['4d74c22109fe9f110579f77b053b8bc3', 'ed74c22109fe9f110579f77b053b8bc3'],
                    'algorithm' => 'md5',
                ],
                $testFile,
                true,
                null,
            ],
            [
                [
                    'hash'      => ['4d74c22109fe9f110579f77b053b8bc3', '7d74c22109fe9f110579f77b053b8bc3'],
                    'algorithm' => 'md5',
                ],
                $testFile,
                false,
                Hash::DOES_NOT_MATCH,
            ],
            [['hash' => 'ffeb8d5d'], __DIR__ . '/_files/testsize.mo', true, null],
            [['hash' => '9f8d07e2'], __DIR__ . '/_files/testsize.mo', false, Hash::DOES_NOT_MATCH],
        ];

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
                continue;
            }

            $psrUpload = new UploadedFile(
                $data[1],
                200,
                UPLOAD_ERR_OK,
                'Foo.txt',
                'text/plain',
            );

            $testData[] = [$data[0], $psrUpload, $data[2], $data[3]];
        }

        return $testData;
    }

    /**
     * Ensures that the validator follows expected behavior
     *
     * @param OptionsArgument $options
     */
    #[DataProvider('basicBehaviorDataProvider')]
    public function testBasic($options, mixed $value, bool $expected, ?string $messageKey): void
    {
        $validator = new Hash($options);

        self::assertSame($expected, $validator->isValid($value));

        if (! $expected && $messageKey !== null) {
            self::assertArrayHasKey($messageKey, $validator->getMessages());
        }
    }

    public function testUnsupportedAlgoIsExceptional(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Unknown algorithm 'muppets'");
        new Hash(['hash' => 'foo', 'algorithm' => 'muppets']);
    }

    public function testMissingHashIsExceptional(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Files cannot be validated without a hash specified');
        new Hash(['hash' => []]);
    }

    public function testUnreadableFile(): void
    {
        $validator = new Hash(['hash' => 'boogers']);

        $path = __DIR__ . '/_files/no-read.txt';
        touch($path);
        chmod($path, 0333);
        try {
            self::assertFalse($validator->isValid($path));
            $messages = $validator->getMessages();
            self::assertArrayHasKey(Hash::NOT_FOUND, $messages);
        } finally {
            unlink($path);
        }
    }
}
