<?php

declare(strict_types=1);

namespace LaminasTest\Validator\File;

use Laminas\Diactoros\UploadedFile;
use Laminas\Validator\File\Exists;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

use function basename;
use function current;
use function dirname;
use function implode;

use const UPLOAD_ERR_NO_FILE;
use const UPLOAD_ERR_OK;

/** @psalm-import-type OptionsArgument from Exists */
final class ExistsTest extends TestCase
{
    /**
     * phpcs:disable Generic.Files.LineLength
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
            'String file name, not found in directory'             => [['directory' => dirname($baseDir)], $baseName, false],
            'String file name, found in directory'                 => [['directory' => $baseDir], $baseName, true],
            'String path, directory option irrelevant'             => [['directory' => $baseDir], $testFile, true],
            'String path, no directory option'                     => [[], $testFile, true],
            '$_FILES with path. Not found in dir option'           => [['directory' => dirname($baseDir)], $fileUpload, false],
            '$_FILES with path. No Directory option'               => [[], $fileUpload, true],
            'PSR with path. No Directory option'                   => [[], $psrUpload, true],
            'PSR with path. Not found in directory option'         => [['directory' => '/home/whatever'], $psrUpload, false],
            'String filename, found in one of the listed dirs'     => [['directory' => $directoryList, 'all' => false], 'picture.jpg', true],
            'String filename, found in one of CSV dirs'            => [['directory' => $csv, 'all' => false], 'picture.jpg', true],
            'String filename, not found in all the listed dirs'    => [['directory' => $directoryList], 'picture.jpg', false],
            'String filename, not found in all the CSV dirs'       => [['directory' => $csv], 'picture.jpg', false],
            'String filename, not found in any of the listed dirs' => [['directory' => $directoryList], 'nope.jpg', false],
            'String filename, not found in CSV dirs'               => [['directory' => $csv], 'nope.jpg', false],
            'Not a string, can’t exist'                            => [['directory' => __DIR__], 123, false],
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
        $validator = new Exists($options);

        self::assertSame($expected, $validator->isValid($value));
    }

    #[Group('Laminas-11258')]
    public function testLaminas11258(): void
    {
        $validator = new Exists();

        self::assertFalse($validator->isValid('nofile.mo'));
        self::assertArrayHasKey(Exists::DOES_NOT_EXIST, $validator->getMessages());
        self::assertStringContainsString('does not exist', current($validator->getMessages()));
    }

    public function testEmptyFileArrayShouldReturnFalse(): void
    {
        $validator = new Exists();

        self::assertFalse($validator->isValid(''));
        self::assertArrayHasKey(Exists::DOES_NOT_EXIST, $validator->getMessages());

        $filesArray = [
            'name'     => '',
            'size'     => 0,
            'tmp_name' => '',
            'error'    => UPLOAD_ERR_NO_FILE,
            'type'     => '',
        ];

        self::assertFalse($validator->isValid($filesArray));
        self::assertArrayHasKey(Exists::DOES_NOT_EXIST, $validator->getMessages());
    }
}
