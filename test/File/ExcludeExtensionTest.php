<?php

declare(strict_types=1);

namespace LaminasTest\Validator\File;

use Laminas\Validator\Exception\InvalidArgumentException;
use Laminas\Validator\File\ExcludeExtension;
use PHPUnit\Framework\TestCase;

use function array_merge;
use function basename;
use function current;
use function is_array;

use const UPLOAD_ERR_NO_FILE;

/**
 * @group Laminas_Validator
 * @covers \Laminas\Validator\File\ExcludeExtension
 */
final class ExcludeExtensionTest extends TestCase
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
    public function basicBehaviorDataProvider(): array
    {
        $testFile     = __DIR__ . '/_files/testsize.mo';
        $pictureTests = [
            //    Options, isValid Param, Expected value, Expected message
            ['mo',                  $testFile, false,  'fileExcludeExtensionFalse'],
            ['gif',                 $testFile, true, ''],
            [['mo'], $testFile, false, 'fileExcludeExtensionFalse'],
            [['gif'], $testFile, true, ''],
            [['gif', 'mo', 'pict'], $testFile, false, 'fileExcludeExtensionFalse'],
            [['gif', 'gz', 'hint'], $testFile, true, ''],
        ];

        $testFile    = __DIR__ . '/_files/nofile.mo';
        $noFileTests = [
            //    Options, isValid Param, Expected value, message
            ['mo', $testFile, false, 'fileExcludeExtensionNotFound'],
            [['extension' => 'gif', 'allowNonExistentFile' => true], $testFile, true, ''],
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
     * @dataProvider basicBehaviorDataProvider
     * @param string|string[] $options
     * @param string|array $isValidParam
     */
    public function testBasic($options, $isValidParam, bool $expected, string $messageKey): void
    {
        $validator = new ExcludeExtension($options);

        self::assertSame($expected, $validator->isValid($isValidParam));

        if (! $expected) {
            self::assertArrayHasKey($messageKey, $validator->getMessages());
        }
    }

    /**
     * Ensures that the validator follows expected behavior for legacy Laminas\Transfer API
     *
     * @dataProvider basicBehaviorDataProvider
     * @param string|string[] $options
     * @param string|array $isValidParam
     */
    public function testLegacy($options, $isValidParam, bool $expected, string $messageKey): void
    {
        if (! is_array($isValidParam)) {
            self::markTestSkipped('An array is expected for legacy compat tests');
        }

        $validator = new ExcludeExtension($options);

        self::assertSame($expected, $validator->isValid($isValidParam['tmp_name'], $isValidParam));

        if (! $expected) {
            self::assertArrayHasKey($messageKey, $validator->getMessages());
        }
    }

    public function testCaseTesting(): void
    {
        $files     = [
            'name'     => 'testsize.mo',
            'type'     => 'text',
            'size'     => 200,
            'tmp_name' => __DIR__ . '/_files/testsize.mo',
            'error'    => 0,
        ];
        $validator = new ExcludeExtension(['MO', 'case' => true]);

        self::assertTrue($validator->isValid(__DIR__ . '/_files/testsize.mo', $files));

        $validator = new ExcludeExtension(['MO', 'case' => false]);

        self::assertFalse($validator->isValid(__DIR__ . '/_files/testsize.mo', $files));
    }

    /** @psalm-return array<array{string|string[], string[]}> */
    public function getExtensionProvider(): array
    {
        return [
            ['mo', ['mo']],
            [['mo', 'gif', 'jpg'], ['mo', 'gif', 'jpg']],
        ];
    }

    /**
     * Ensures that getExtension() returns expected value
     *
     * @dataProvider getExtensionProvider
     * @param string|string[] $extension
     * @param string[] $expected
     */
    public function testGetExtension($extension, array $expected): void
    {
        $validator = new ExcludeExtension($extension);

        self::assertSame($expected, $validator->getExtension());
    }

    /** @psalm-return array<array{string|string[], string[]}> */
    public function setExtensionProvider(): array
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
     * @dataProvider setExtensionProvider
     * @param string|string[] $extension
     * @param string[] $expected
     */
    public function testSetExtension($extension, array $expected): void
    {
        $validator = new ExcludeExtension('mo');
        $validator->setExtension($extension);

        self::assertSame($expected, $validator->getExtension());
    }

    /**
     * Ensures that addExtension() returns expected value
     */
    public function testAddExtension(): void
    {
        $validator = new ExcludeExtension('mo');
        $validator->addExtension('gif');

        self::assertSame(['mo', 'gif'], $validator->getExtension());

        $validator->addExtension('jpg, to');

        self::assertSame(['mo', 'gif', 'jpg', 'to'], $validator->getExtension());

        $validator->addExtension(['zip', 'ti']);

        self::assertSame(['mo', 'gif', 'jpg', 'to', 'zip', 'ti'], $validator->getExtension());

        $validator->addExtension('');

        self::assertSame(['mo', 'gif', 'jpg', 'to', 'zip', 'ti'], $validator->getExtension());
    }

    /**
     * @group Laminas-11258
     */
    public function testLaminas11258(): void
    {
        $validator = new ExcludeExtension('mo');

        self::assertFalse($validator->isValid(__DIR__ . '/_files/nofile.mo'));
        self::assertArrayHasKey('fileExcludeExtensionNotFound', $validator->getMessages());
        self::assertStringContainsString('does not exist', current($validator->getMessages()));
    }

    public function testEmptyFileShouldReturnFalseAndDisplayNotFoundMessage(): void
    {
        $validator = new ExcludeExtension('12345');

        self::assertFalse($validator->isValid(''));
        self::assertArrayHasKey(ExcludeExtension::NOT_FOUND, $validator->getMessages());

        $filesArray = [
            'name'     => '',
            'size'     => 0,
            'tmp_name' => '',
            'error'    => UPLOAD_ERR_NO_FILE,
            'type'     => '',
        ];

        self::assertFalse($validator->isValid($filesArray));
        self::assertArrayHasKey(ExcludeExtension::NOT_FOUND, $validator->getMessages());
    }

    public function testIsValidRaisesExceptionWithArrayNotInFilesFormat(): void
    {
        $validator = new ExcludeExtension('12345');
        $value     = ['foo' => 'bar'];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Value array must be in $_FILES format');

        $validator->isValid($value);
    }
}
