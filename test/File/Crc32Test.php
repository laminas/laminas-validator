<?php

/**
 * @see       https://github.com/laminas/laminas-validator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-validator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-validator/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Validator\File;

use Laminas\Validator\Exception\InvalidArgumentException;
use Laminas\Validator\File;
use PHPUnit\Framework\TestCase;

/**
 * @group      Laminas_Validator
 */
class Crc32Test extends TestCase
{
    /**
     * @return array
     */
    public function basicBehaviorDataProvider()
    {
        $testFile = __DIR__ . '/_files/picture.jpg';
        $pictureTests = [
            //    Options, isValid Param, Expected value, Expected message
            ['3f8d07e2',               $testFile, true, ''],
            ['9f8d07e2',               $testFile, false, 'fileCrc32DoesNotMatch'],
            [['9f8d07e2', '3f8d07e2'], $testFile, true, ''],
            [['9f8d07e2', '7f8d07e2'], $testFile, false, 'fileCrc32DoesNotMatch'],
        ];

        $testFile = __DIR__ . '/_files/nofile.mo';
        $noFileTests = [
            //    Options, isValid Param, Expected value, message
            ['3f8d07e2', $testFile, false, 'fileCrc32NotFound'],
        ];

        $testFile = __DIR__ . '/_files/testsize.mo';
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
     * @return void
     */
    public function testBasic($options, $isValidParam, $expected, $messageKey)
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
     * @return void
     */
    public function testLegacy($options, $isValidParam, $expected, $messageKey)
    {
        if (is_array($isValidParam)) {
            $validator = new File\Crc32($options);
            $this->assertEquals($expected, $validator->isValid($isValidParam['tmp_name'], $isValidParam));
            if (! $expected) {
                $this->assertArrayHasKey($messageKey, $validator->getMessages());
            }
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
     *
     * @return void
     */
    public function testgetHash()
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
     *
     * @return void
     */
    public function testSetHash()
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
     *
     * @return void
     */
    public function testAddHash()
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
    public function testLaminas11258()
    {
        $validator = new File\Crc32('3f8d07e2');
        $this->assertFalse($validator->isValid(__DIR__ . '/_files/nofile.mo'));
        $this->assertArrayHasKey('fileCrc32NotFound', $validator->getMessages());
        $this->assertStringContainsString('does not exist', current($validator->getMessages()));
    }

    public function testEmptyFileShouldReturnFalseAndDisplayNotFoundMessage()
    {
        $validator = new File\Crc32();

        $this->assertFalse($validator->isValid(''));
        $this->assertArrayHasKey(File\Crc32::NOT_FOUND, $validator->getMessages());

        $filesArray = [
            'name'      => '',
            'size'      => 0,
            'tmp_name'  => '',
            'error'     => UPLOAD_ERR_NO_FILE,
            'type'      => '',
        ];

        $this->assertFalse($validator->isValid($filesArray));
        $this->assertArrayHasKey(File\Crc32::NOT_FOUND, $validator->getMessages());
    }

    public function testShouldThrowInvalidArgumentExceptionForArrayValueNotInFilesFormat()
    {
        $validator    = new File\Crc32();
        $invalidArray = ['foo' => 'bar'];
        $this->expectException(InvalidArgumentException::class);
        $validator->isValid($invalidArray);
    }
}
