<?php

namespace LaminasTest\Validator\File;

use Laminas\Validator\Exception\InvalidArgumentException;
use Laminas\Validator\File;
use PHPUnit\Framework\TestCase;

use function array_merge;
use function basename;
use function current;
use function is_array;

use const UPLOAD_ERR_NO_FILE;

/**
 * @group      Laminas_Validator
 */
class ExcludeExtensionTest extends TestCase
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
        $validator = new File\ExcludeExtension($options);
        $this->assertEquals($expected, $validator->isValid($isValidParam));
        if (! $expected) {
            $this->assertArrayHasKey($messageKey, $validator->getMessages());
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
        if (is_array($isValidParam)) {
            $validator = new File\ExcludeExtension($options);
            $this->assertEquals($expected, $validator->isValid($isValidParam['tmp_name'], $isValidParam));
            if (! $expected) {
                $this->assertArrayHasKey($messageKey, $validator->getMessages());
            }
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
        $validator = new File\ExcludeExtension(['MO', 'case' => true]);
        $this->assertEquals(true, $validator->isValid(__DIR__ . '/_files/testsize.mo', $files));

        $validator = new File\ExcludeExtension(['MO', 'case' => false]);
        $this->assertEquals(false, $validator->isValid(__DIR__ . '/_files/testsize.mo', $files));
    }

    /**
     * Ensures that getExtension() returns expected value
     *
     * @return void
     */
    public function testGetExtension()
    {
        $validator = new File\ExcludeExtension('mo');
        $this->assertEquals(['mo'], $validator->getExtension());

        $validator = new File\ExcludeExtension(['mo', 'gif', 'jpg']);
        $this->assertEquals(['mo', 'gif', 'jpg'], $validator->getExtension());
    }

    /**
     * Ensures that setExtension() returns expected value
     *
     * @return void
     */
    public function testSetExtension()
    {
        $validator = new File\ExcludeExtension('mo');
        $validator->setExtension('gif');
        $this->assertEquals(['gif'], $validator->getExtension());

        $validator->setExtension('jpg, mo');
        $this->assertEquals(['jpg', 'mo'], $validator->getExtension());

        $validator->setExtension(['zip', 'ti']);
        $this->assertEquals(['zip', 'ti'], $validator->getExtension());
    }

    /**
     * Ensures that addExtension() returns expected value
     *
     * @return void
     */
    public function testAddExtension()
    {
        $validator = new File\ExcludeExtension('mo');
        $validator->addExtension('gif');
        $this->assertEquals(['mo', 'gif'], $validator->getExtension());

        $validator->addExtension('jpg, to');
        $this->assertEquals(['mo', 'gif', 'jpg', 'to'], $validator->getExtension());

        $validator->addExtension(['zip', 'ti']);
        $this->assertEquals(['mo', 'gif', 'jpg', 'to', 'zip', 'ti'], $validator->getExtension());

        $validator->addExtension('');
        $this->assertEquals(['mo', 'gif', 'jpg', 'to', 'zip', 'ti'], $validator->getExtension());
    }

    /**
     * @group Laminas-11258
     */
    public function testLaminas11258(): void
    {
        $validator = new File\ExcludeExtension('mo');
        $this->assertEquals(false, $validator->isValid(__DIR__ . '/_files/nofile.mo'));
        $this->assertArrayHasKey('fileExcludeExtensionNotFound', $validator->getMessages());
        $this->assertStringContainsString('does not exist', current($validator->getMessages()));
    }

    public function testEmptyFileShouldReturnFalseAndDisplayNotFoundMessage(): void
    {
        $validator = new File\ExcludeExtension('12345');

        $this->assertFalse($validator->isValid(''));
        $this->assertArrayHasKey(File\ExcludeExtension::NOT_FOUND, $validator->getMessages());

        $filesArray = [
            'name'     => '',
            'size'     => 0,
            'tmp_name' => '',
            'error'    => UPLOAD_ERR_NO_FILE,
            'type'     => '',
        ];

        $this->assertFalse($validator->isValid($filesArray));
        $this->assertArrayHasKey(File\ExcludeExtension::NOT_FOUND, $validator->getMessages());
    }

    public function testIsValidRaisesExceptionWithArrayNotInFilesFormat(): void
    {
        $validator = new File\ExcludeExtension('12345');
        $value     = ['foo' => 'bar'];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Value array must be in $_FILES format');

        $validator->isValid($value);
    }
}
