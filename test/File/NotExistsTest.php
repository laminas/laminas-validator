<?php

declare(strict_types=1);

namespace LaminasTest\Validator\File;

use Laminas\Diactoros\UploadedFile;
use Laminas\Validator\File\NotExists;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

use function basename;
use function current;
use function dirname;
use function implode;

use const UPLOAD_ERR_OK;

/** @psalm-import-type OptionsArgument from NotExists */
final class NotExistsTest extends TestCase
{
    /**
     * phpcs:disable Generic.Files.LineLength
     *
     * @psalm-return array<string, array{
     *     0: OptionsArgument,
     *     1: mixed,
     *     2: bool
     * }>
     */
    public static function basicBehaviorDataProvider(): array
    {
        $testFile   = __DIR__ . '/_files/testsize.mo';
        $baseDir    = dirname($testFile);
        $baseName   = basename($testFile);
        $fileUpload = [
            'tmp_name' => $testFile,
            'name'     => basename($testFile),
            'size'     => 200,
            'error'    => UPLOAD_ERR_OK,
            'type'     => 'text',
        ];
        $psrUpload  = new UploadedFile(
            $testFile,
            200,
            UPLOAD_ERR_OK,
            'foo.txt',
            'text/plain',
        );

        $directoryList = [
            __DIR__,
            __DIR__ . '/_files',
            dirname(__DIR__),
        ];

        $csv = implode(', ', $directoryList);

        return [
            'String file name, not found in directory'             => [['directory' => dirname($baseDir)], $baseName, true],
            'String file name, found in directory'                 => [['directory' => $baseDir], $baseName, false],
            'String path, directory option irrelevant'             => [['directory' => $baseDir], $testFile, false],
            'String path, no directory option'                     => [[], $testFile, false],
            '$_FILES. No directory option'                         => [[], $fileUpload, false],
            '$_FILES. Basename located in directory'               => [['directory' => $baseDir], $fileUpload, false],
            '$_FILES. Basename not located in directory'           => [['directory' => dirname($baseDir)], $fileUpload, true],
            'PSR. No Directory option'                             => [[], $psrUpload, false],
            'PSR. Basename located in directory'                   => [['directory' => $baseDir], $psrUpload, false],
            'PSR. Basename not located in directory'               => [['directory' => dirname($baseDir)], $psrUpload, true],
            'String filename, found in one of the listed dirs'     => [['directory' => $directoryList], 'picture.jpg', false],
            'String filename, found in CSV dirs'                   => [['directory' => $csv], 'picture.jpg', false],
            'String filename, not found in one of the listed dirs' => [['directory' => $directoryList], 'nope.jpg', true],
            'String filename, not found in CSV dirs'               => [['directory' => $csv], 'nope.jpg', true],
            'Not a string, can’t exist'                            => [['directory' => __DIR__], 123, true],
        ];
    }

    /**
     * Ensures that the validator follows expected behavior
     *
     * @param OptionsArgument $options
     */
    #[DataProvider('basicBehaviorDataProvider')]
    public function testBasic(array $options, mixed $value, bool $expected): void
    {
        $validator = new NotExists($options);

        self::assertSame($expected, $validator->isValid($value));
    }

    #[Group('Laminas-11258')]
    public function testLaminas11258(): void
    {
        $validator = new NotExists();

        self::assertFalse($validator->isValid(__DIR__ . '/_files/testsize.mo'));
        self::assertArrayHasKey(NotExists::DOES_EXIST, $validator->getMessages());
        self::assertStringContainsString('File exists', current($validator->getMessages()));
    }
}
