<?php

declare(strict_types=1);

namespace LaminasTest\Validator\File;

use Laminas\Diactoros\UploadedFile;
use Laminas\Validator\Exception\InvalidArgumentException;
use Laminas\Validator\File\Extension;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use const UPLOAD_ERR_OK;

/** @psalm-import-type OptionsArgument from Extension */
final class ExtensionTest extends TestCase
{
    /**
     * @psalm-return iterable<string, array{
     *     0: OptionsArgument,
     *     1: mixed,
     *     2: bool,
     *     3: string|null,
     * }>
     */
    public static function basicBehaviorDataProvider(): iterable
    {
        yield 'String filename found in allowed list' => [
            ['extension' => 'tar.gz', 'case' => false, 'allowNonExistentFile' => true],
            'SomeFile.tar.gz',
            true,
            null,
        ];

        yield 'String filename found in allowed list, mismatched case' => [
            ['extension' => 'GZ,ZIP', 'case' => true, 'allowNonExistentFile' => true],
            'SomeFile.tar.gz',
            false,
            Extension::FALSE_EXTENSION,
        ];

        yield 'String filename found in allowed list, matched case' => [
            ['extension' => 'GZ,ZIP', 'case' => true, 'allowNonExistentFile' => true],
            'SHOUTING.ZIP',
            true,
            null,
        ];

        yield 'String filename not found in allowed list' => [
            ['extension' => 'zip,txt,pdf', 'case' => false, 'allowNonExistentFile' => true],
            'SomeFile.tar.gz',
            false,
            Extension::FALSE_EXTENSION,
        ];

        yield 'Non-existent file as string' => [
            ['extension' => 'txt'],
            'Some-File.txt',
            false,
            Extension::NOT_FOUND,
        ];

        yield 'Non string and non upload value' => [
            ['extension' => 'txt', 'allowNonExistentFile' => true],
            true,
            false,
            Extension::ERROR_INVALID_TYPE,
        ];

        $existingFileAsUploadArray = [
            'tmp_name' => __DIR__ . '/_files/picture.jpg',
            'name'     => 'picture.jpg',
            'size'     => 200,
            'error'    => UPLOAD_ERR_OK,
            'type'     => 'image/jpeg',
        ];

        yield 'PHP Upload with real file and matching extension' => [
            ['extension' => 'jpg,png,gif'],
            $existingFileAsUploadArray,
            true,
            null,
        ];

        yield 'PHP Upload with real file and non-matching extension' => [
            ['extension' => 'txt,pdf'],
            $existingFileAsUploadArray,
            false,
            Extension::FALSE_EXTENSION,
        ];

        $psrUpload = new UploadedFile(
            __DIR__ . '/_files/picture.jpg',
            200,
            UPLOAD_ERR_OK,
            'picture.jpg',
            'image/jpeg',
        );

        yield 'PSR Upload with real file and matching extension' => [
            ['extension' => 'jpg,png,gif'],
            $psrUpload,
            true,
            null,
        ];

        yield 'PSR Upload with real file and non-matching extension' => [
            ['extension' => 'txt,pdf'],
            $psrUpload,
            false,
            Extension::FALSE_EXTENSION,
        ];

        yield 'Numeric extension comparison failure' => [
            ['extension' => '010', 'allowNonExistentFile' => true],
            'some-file.10',
            false,
            Extension::FALSE_EXTENSION,
        ];

        yield 'Numeric extension comparison success' => [
            ['extension' => '010', 'allowNonExistentFile' => true],
            'some-file.010',
            true,
            null,
        ];
    }

    /**
     * Ensures that the validator follows expected behavior
     *
     * @param OptionsArgument $options
     */
    #[DataProvider('basicBehaviorDataProvider')]
    public function testBasic(array $options, mixed $value, bool $expected, string|null $messageKey): void
    {
        $validator = new Extension($options);

        self::assertSame($expected, $validator->isValid($value));

        if (! $expected && $messageKey !== null) {
            self::assertArrayHasKey($messageKey, $validator->getMessages());
        }
    }

    public function testExtensionListIsARequiredOption(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The extension option must resolve to a non-empty list');

        /** @psalm-suppress InvalidArgument */
        new Extension([]);
    }

    /** @return array<string, array{0: string|list<string>, 1: list<string>}> */
    public static function extensionOptions(): array
    {
        return [
            'CSV'                   => ['foo,bar,baz', ['foo', 'bar', 'baz']],
            'CSV with empty values' => ['foo,,,bar', ['foo', 'bar']],
            'Single string'         => ['baz', ['baz']],
            'Single array element'  => [['foo'], ['foo']],
            'List'                  => [['a', 'b', 'c'], ['a', 'b', 'c']],
            'List with empties'     => [['a', ''], ['a']],
        ];
    }

    /**
     * @param string|array<array-key, string> $input
     * @param list<string> $expect
     * @psalm-suppress InternalMethod
     */
    #[DataProvider('extensionOptions')]
    public function testResolveExtensionList(string|array $input, array $expect): void
    {
        self::assertSame($expect, Extension::resolveExtensionList($input));
    }

    /** @return array<string, array{0: string|array<array-key, string>}> */
    public static function invalidExtensionOptions(): array
    {
        return [
            'Empty Array'                  => [[]],
            'Only Commas'                  => [',,,,'],
            'Empty String'                 => [''],
            'List with only empty strings' => [['', '']],
        ];
    }

    /**
     * @param string|array<array-key, string> $list
     * @psalm-suppress InternalMethod
     */
    #[DataProvider('invalidExtensionOptions')]
    public function testResolveExtensionListIsExceptional(string|array $list): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The extension option must resolve to a non-empty list');

        Extension::resolveExtensionList($list);
    }

    /** @return list<array{0: non-empty-string, 1: list<string>}> */
    public static function fileNameExtensionProvider(): array
    {
        return [
            ['file.tar.gz', ['gz', 'tar.gz']],
            ['file...txt', ['txt']],
            ['file..tar...gz', ['gz', 'tar.gz']],
            ['file.bar.baz.pdf', ['pdf', 'baz.pdf', 'bar.baz.pdf']],
        ];
    }

    /**
     * @param non-empty-string $input
     * @param list<string> $expect
     * @psalm-suppress InternalMethod
     */
    #[DataProvider('fileNameExtensionProvider')]
    public function testListPossibleFileNameExtensions(string $input, array $expect): void
    {
        self::assertSame($expect, Extension::listPossibleFileNameExtensions($input));
    }
}
