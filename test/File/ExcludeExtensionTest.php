<?php

declare(strict_types=1);

namespace LaminasTest\Validator\File;

use Laminas\Diactoros\UploadedFile;
use Laminas\Validator\Exception\InvalidArgumentException;
use Laminas\Validator\File\ExcludeExtension;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use const UPLOAD_ERR_OK;

/** @psalm-import-type OptionsArgument from ExcludeExtension */
final class ExcludeExtensionTest extends TestCase
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
        yield 'String filename found in disallowed list' => [
            ['extension' => 'tar.gz', 'case' => false, 'allowNonExistentFile' => true],
            'SomeFile.tar.gz',
            false,
            ExcludeExtension::FALSE_EXTENSION,
        ];

        yield 'String filename found in disallowed list, mismatched case' => [
            ['extension' => 'GZ,ZIP', 'case' => true, 'allowNonExistentFile' => true],
            'SomeFile.tar.gz',
            true,
            null,
        ];

        yield 'String filename found in disallowed list, matched case' => [
            ['extension' => 'GZ,ZIP', 'case' => true, 'allowNonExistentFile' => true],
            'SHOUTING.ZIP',
            false,
            ExcludeExtension::FALSE_EXTENSION,
        ];

        yield 'String filename not found in disallowed list' => [
            ['extension' => 'zip,txt,pdf', 'case' => false, 'allowNonExistentFile' => true],
            'SomeFile.tar.gz',
            true,
            null,
        ];

        yield 'Non-existent file as string' => [
            ['extension' => 'txt'],
            'Some-File.txt',
            false,
            ExcludeExtension::NOT_FOUND,
        ];

        yield 'Non string and non upload value' => [
            ['extension' => 'txt', 'allowNonExistentFile' => true],
            true,
            false,
            ExcludeExtension::ERROR_INVALID_TYPE,
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
            false,
            ExcludeExtension::FALSE_EXTENSION,
        ];

        yield 'PHP Upload with real file and non-matching extension' => [
            ['extension' => 'txt,pdf'],
            $existingFileAsUploadArray,
            true,
            null,
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
            false,
            ExcludeExtension::FALSE_EXTENSION,
        ];

        yield 'PSR Upload with real file and non-matching extension' => [
            ['extension' => 'txt,pdf'],
            $psrUpload,
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
        $validator = new ExcludeExtension($options);

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
        new ExcludeExtension([]);
    }
}
