<?php

declare(strict_types=1);

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
class Crc32Test extends TestCase
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
        $testFile     = __DIR__ . '/_files/picture.jpg';
        $pictureTests = [
            //    Options, isValid Param, Expected value, Expected message
            ['3f8d07e2',               $testFile, true, ''],
            ['9f8d07e2',               $testFile, false, 'fileCrc32DoesNotMatch'],
            [['9f8d07e2', '3f8d07e2'], $testFile, true, ''],
            [['9f8d07e2', '7f8d07e2'], $testFile, false, 'fileCrc32DoesNotMatch'],
        ];

        $testFile    = __DIR__ . '/_files/nofile.mo';
        $noFileTests = [
            //    Options, isValid Param, Expected value, message
            ['3f8d07e2', $testFile, false, 'fileCrc32NotFound'],
        ];

        $testFile      = __DIR__ . '/_files/testsize.mo';
        $sizeFileTests = [
            //    Options, isValid Param, Expected value, message
            ['ffeb8d5d', $testFile, true,  ''],
            ['9f8d07e2', $testFile, false, 'fileCrc32DoesNotMatch'],
        ];

        // Dupe data in File Upload format
        $testData = array_merge($pictureTests, $noFileTests, $sizeFileTests);
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
        $validator = new File\Crc32($options);
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
        if (! is_array($isValidParam)) {
            $this->markTestSkipped('An array is expected for legacy compat tests');
        }

        $validator = new File\Crc32($options);
        $this->assertEquals($expected, $validator->isValid($isValidParam['tmp_name'], $isValidParam));
        if (! $expected) {
            $this->assertArrayHasKey($messageKey, $validator->getMessages());
        }
    }

    /**
     * Ensures that getCrc32() returns expected value
     *
     * @return void
     */
    public function testgetCrc32()
    {
        $validator = new File\Crc32('12345');
        $this->assertEquals(['12345' => 'crc32'], $validator->getCrc32());

        $validator = new File\Crc32(['12345', '12333', '12344']);
        $this->assertEquals(['12345' => 'crc32', '12333' => 'crc32', '12344' => 'crc32'], $validator->getCrc32());
    }

    /**
     * Ensures that getHash() returns expected value
     */
    public function testgetHash(): void
    {
        $validator = new File\Crc32('12345');
        $this->assertEquals(['12345' => 'crc32'], $validator->getHash());

        $validator = new File\Crc32(['12345', '12333', '12344']);
        $this->assertEquals(['12345' => 'crc32', '12333' => 'crc32', '12344' => 'crc32'], $validator->getHash());
    }

    /**
     * Ensures that setCrc32() returns expected value
     *
     * @return void
     */
    public function testSetCrc32()
    {
        $validator = new File\Crc32('12345');
        $validator->setCrc32('12333');
        $this->assertEquals(['12333' => 'crc32'], $validator->getCrc32());

        $validator->setCrc32(['12321', '12121']);
        $this->assertEquals(['12321' => 'crc32', '12121' => 'crc32'], $validator->getCrc32());
    }

    /**
     * Ensures that setHash() returns expected value
     */
    public function testSetHash(): void
    {
        $validator = new File\Crc32('12345');
        $validator->setHash('12333');
        $this->assertEquals(['12333' => 'crc32'], $validator->getCrc32());

        $validator->setHash(['12321', '12121']);
        $this->assertEquals(['12321' => 'crc32', '12121' => 'crc32'], $validator->getCrc32());
    }

    /**
     * Ensures that addCrc32() returns expected value
     *
     * @return void
     */
    public function testAddCrc32()
    {
        $validator = new File\Crc32('12345');
        $validator->addCrc32('12344');
        $this->assertEquals(['12345' => 'crc32', '12344' => 'crc32'], $validator->getCrc32());

        $validator->addCrc32(['12321', '12121']);
        $this->assertEquals(
            ['12345' => 'crc32', '12344' => 'crc32', '12321' => 'crc32', '12121' => 'crc32'],
            $validator->getCrc32()
        );
    }

    /**
     * Ensures that addHash() returns expected value
     */
    public function testAddHash(): void
    {
        $validator = new File\Crc32('12345');
        $validator->addHash('12344');
        $this->assertEquals(['12345' => 'crc32', '12344' => 'crc32'], $validator->getCrc32());

        $validator->addHash(['12321', '12121']);
        $this->assertEquals(
            ['12345' => 'crc32', '12344' => 'crc32', '12321' => 'crc32', '12121' => 'crc32'],
            $validator->getCrc32()
        );
    }

    /**
     * @group Laminas-11258
     */
    public function testLaminas11258(): void
    {
        $validator = new File\Crc32('3f8d07e2');
        $this->assertFalse($validator->isValid(__DIR__ . '/_files/nofile.mo'));
        $this->assertArrayHasKey('fileCrc32NotFound', $validator->getMessages());
        $this->assertStringContainsString('does not exist', current($validator->getMessages()));
    }

    public function testEmptyFileShouldReturnFalseAndDisplayNotFoundMessage(): void
    {
        $validator = new File\Crc32();

        $this->assertFalse($validator->isValid(''));
        $this->assertArrayHasKey(File\Crc32::NOT_FOUND, $validator->getMessages());

        $filesArray = [
            'name'     => '',
            'size'     => 0,
            'tmp_name' => '',
            'error'    => UPLOAD_ERR_NO_FILE,
            'type'     => '',
        ];

        $this->assertFalse($validator->isValid($filesArray));
        $this->assertArrayHasKey(File\Crc32::NOT_FOUND, $validator->getMessages());
    }

    public function testShouldThrowInvalidArgumentExceptionForArrayValueNotInFilesFormat(): void
    {
        $validator    = new File\Crc32();
        $invalidArray = ['foo' => 'bar'];
        $this->expectException(InvalidArgumentException::class);
        $validator->isValid($invalidArray);
    }
}
