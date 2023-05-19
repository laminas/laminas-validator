<?php

declare(strict_types=1);

namespace LaminasTest\Validator\File;

use Laminas\Validator\Exception\InvalidArgumentException;
use Laminas\Validator\File\Extension;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

use function array_merge;
use function basename;
use function current;
use function is_array;

use const UPLOAD_ERR_NO_FILE;

final class ExtensionTest extends TestCase
{
    /**
     * @psalm-return array<array-key, array{
     *     0: string|string[],
     *     1: string|array{
     *         tmp_name: string,
     *         name: string,
     *         size: int,
     *         error: int,
     *         type: string
     *     },
     *     2: bool,
     *     3: string
     * }>
     */
    public static function basicBehaviorDataProvider(): array
    {
        $testFile     = __DIR__ . '/_files/testsize.mo';
        $pictureTests = [
            //    Options, isValid Param, Expected value, Expected message
            ['mo',                  $testFile, true,  ''],
            ['gif',                 $testFile, false, 'fileExtensionFalse'],
            [['mo'], $testFile, true, ''],
            [['gif'], $testFile, false, 'fileExtensionFalse'],
            [['gif', 'mo', 'pict'], $testFile, true, ''],
            [['gif', 'gz', 'hint'], $testFile, false, 'fileExtensionFalse'],
        ];

        $testFile    = __DIR__ . '/_files/nofile.mo';
        $noFileTests = [
            //    Options, isValid Param, Expected value, message
            ['mo', $testFile, false, 'fileExtensionNotFound'],
            [['extension' => 'mo', 'allowNonExistentFile' => true], $testFile, true, ''],
        ];

        // Dupe data in File Upload format
        $testData = array_merge($pictureTests, $noFileTests);
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
     * @param string|string[] $options,
     * @param string|array $isValidParam
     */
    #[DataProvider('basicBehaviorDataProvider')]
    public function testBasic($options, $isValidParam, bool $expected, string $messageKey): void
    {
        $validator = new Extension($options);

        self::assertSame($expected, $validator->isValid($isValidParam));

        if (! $expected) {
            self::assertArrayHasKey($messageKey, $validator->getMessages());
        }
    }

    /**
     * Ensures that the validator follows expected behavior for legacy Laminas\Transfer API
     *
     * @param string|string[] $options,
     * @param string|array $isValidParam
     */
    #[DataProvider('basicBehaviorDataProvider')]
    public function testLegacy($options, $isValidParam, bool $expected, string $messageKey): void
    {
        if (! is_array($isValidParam)) {
            self::markTestSkipped('An array is expected for legacy compat tests');
        }

        $validator = new Extension($options);

        self::assertSame($expected, $validator->isValid($isValidParam['tmp_name'], $isValidParam));

        if (! $expected) {
            self::assertArrayHasKey($messageKey, $validator->getMessages());
        }
    }

    public function testLaminas891(): void
    {
        $files     = [
            'name'     => 'testsize.mo',
            'type'     => 'text',
            'size'     => 200,
            'tmp_name' => __DIR__ . '/_files/testsize.mo',
            'error'    => 0,
        ];
        $validator = new Extension(['MO', 'case' => true]);

        self::assertFalse($validator->isValid(__DIR__ . '/_files/testsize.mo', $files));

        $validator = new Extension(['MO', 'case' => false]);

        self::assertTrue($validator->isValid(__DIR__ . '/_files/testsize.mo', $files));
    }

    /** @psalm-return array<array{string|string[], string[]}> */
    public static function getExtensionProvider(): array
    {
        return [
            ['mo', ['mo']],
            [['mo', 'gif', 'jpg'], ['mo', 'gif', 'jpg']],
        ];
    }

    /**
     * Ensures that getExtension() returns expected value
     *
     * @param string|string[] $extension
     * @param string[] $expected
     */
    #[DataProvider('getExtensionProvider')]
    public function testGetExtension($extension, array $expected): void
    {
        $validator = new Extension($extension);

        self::assertSame($expected, $validator->getExtension());
    }

    /** @psalm-return array<array{string|string[], string[]}> */
    public static function setExtensionProvider(): array
    {
        return [
            ['gif', ['gif']],
            ['jpg, mo', ['jpg', 'mo']],
            [['zip', 'ti'], ['zip', 'ti']],
        ];
    }

    /**
     * Ensures that setExtension() returns expected value
     *
     * @param string|string[] $extension
     * @param string[] $expected
     */
    #[DataProvider('setExtensionProvider')]
    public function testSetExtension($extension, array $expected): void
    {
        $validator = new Extension('mo');
        $validator->setExtension($extension);

        self::assertSame($expected, $validator->getExtension());
    }

    /**
     * Ensures that addExtension() returns expected value
     */
    public function testAddExtension(): void
    {
        $validator = new Extension('mo');
        $validator->addExtension('gif');

        self::assertSame(['mo', 'gif'], $validator->getExtension());

        $validator->addExtension('jpg, to');

        self::assertSame(['mo', 'gif', 'jpg', 'to'], $validator->getExtension());

        $validator->addExtension(['zip', 'ti']);

        self::assertSame(['mo', 'gif', 'jpg', 'to', 'zip', 'ti'], $validator->getExtension());

        $validator->addExtension('');

        self::assertSame(['mo', 'gif', 'jpg', 'to', 'zip', 'ti'], $validator->getExtension());
    }

    #[Group('Laminas-11258')]
    public function testLaminas11258(): void
    {
        $validator = new Extension('gif');

        self::assertFalse($validator->isValid(__DIR__ . '/_files/nofile.mo'));
        self::assertArrayHasKey('fileExtensionNotFound', $validator->getMessages());
        self::assertStringContainsString('does not exist', current($validator->getMessages()));
    }

    public function testEmptyFileShouldReturnFalseAndDisplayNotFoundMessage(): void
    {
        $validator = new Extension('foo');

        self::assertFalse($validator->isValid(''));
        self::assertArrayHasKey(Extension::NOT_FOUND, $validator->getMessages());

        $filesArray = [
            'name'     => '',
            'size'     => 0,
            'tmp_name' => '',
            'error'    => UPLOAD_ERR_NO_FILE,
            'type'     => '',
        ];

        self::assertFalse($validator->isValid($filesArray));
        self::assertArrayHasKey(Extension::NOT_FOUND, $validator->getMessages());
    }

    public function testIsValidRaisesExceptionForArrayNotInFilesFormat(): void
    {
        $validator = new Extension('foo');
        $value     = ['foo' => 'bar'];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Value array must be in $_FILES format');

        $validator->isValid($value);
    }
}
