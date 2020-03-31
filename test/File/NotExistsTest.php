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
 * NotExists testbed
 *
 * @group      Laminas_Validator
 */
class NotExistsTest extends TestCase
{
    /**
     * @return array
     */
    public function basicBehaviorDataProvider()
    {
        $testFile = __DIR__ . '/_files/testsize.mo';
        $baseDir  = dirname($testFile);
        $baseName = basename($testFile);
        $fileUpload = [
            'tmp_name' => $testFile,
            'name'     => basename($testFile),
            'size'     => 200,
            'error'    => 0,
            'type'     => 'text',
        ];

        return [
            //    Options, isValid Param, Expected value
            [dirname($baseDir), $baseName,   true],
            [$baseDir,          $baseName,   false],
            [$baseDir,          $testFile,   false],
            [dirname($baseDir), $fileUpload, true],
            [$baseDir,          $fileUpload, false],
        ];
    }

    /**
     * Ensures that the validator follows expected behavior
     *
     * @dataProvider basicBehaviorDataProvider
     * @return void
     */
    public function testBasic($options, $isValidParam, $expected)
    {
        $validator = new File\NotExists($options);
        $this->assertEquals($expected, $validator->isValid($isValidParam));
    }

    /**
     * Ensures that the validator follows expected behavior for legacy Laminas\Transfer API
     *
     * @dataProvider basicBehaviorDataProvider
     * @return void
     */
    public function testLegacy($options, $isValidParam, $expected)
    {
        if (is_array($isValidParam)) {
            $validator = new File\NotExists($options);
            $this->assertEquals($expected, $validator->isValid($isValidParam['tmp_name'], $isValidParam));
        }
    }

    /**
     * Ensures that getDirectory() returns expected value
     *
     * @return void
     */
    public function testGetDirectory()
    {
        $validator = new File\NotExists('C:/temp');
        $this->assertEquals('C:/temp', $validator->getDirectory());

        $validator = new File\NotExists(['temp', 'dir', 'jpg']);
        $this->assertEquals('temp,dir,jpg', $validator->getDirectory());

        $validator = new File\NotExists(['temp', 'dir', 'jpg']);
        $this->assertEquals(['temp', 'dir', 'jpg'], $validator->getDirectory(true));
    }

    /**
     * Ensures that setDirectory() returns expected value
     *
     * @return void
     */
    public function testSetDirectory()
    {
        $validator = new File\NotExists('temp');
        $validator->setDirectory('gif');
        $this->assertEquals('gif', $validator->getDirectory());
        $this->assertEquals(['gif'], $validator->getDirectory(true));

        $validator->setDirectory('jpg, temp');
        $this->assertEquals('jpg,temp', $validator->getDirectory());
        $this->assertEquals(['jpg', 'temp'], $validator->getDirectory(true));

        $validator->setDirectory(['zip', 'ti']);
        $this->assertEquals('zip,ti', $validator->getDirectory());
        $this->assertEquals(['zip', 'ti'], $validator->getDirectory(true));
    }

    /**
     * Ensures that addDirectory() returns expected value
     *
     * @return void
     */
    public function testAddDirectory()
    {
        $validator = new File\NotExists('temp');
        $validator->addDirectory('gif');
        $this->assertEquals('temp,gif', $validator->getDirectory());
        $this->assertEquals(['temp', 'gif'], $validator->getDirectory(true));

        $validator->addDirectory('jpg, to');
        $this->assertEquals('temp,gif,jpg,to', $validator->getDirectory());
        $this->assertEquals(['temp', 'gif', 'jpg', 'to'], $validator->getDirectory(true));

        $validator->addDirectory(['zip', 'ti']);
        $this->assertEquals('temp,gif,jpg,to,zip,ti', $validator->getDirectory());
        $this->assertEquals(['temp', 'gif', 'jpg', 'to', 'zip', 'ti'], $validator->getDirectory(true));

        $validator->addDirectory('');
        $this->assertEquals('temp,gif,jpg,to,zip,ti', $validator->getDirectory());
        $this->assertEquals(['temp', 'gif', 'jpg', 'to', 'zip', 'ti'], $validator->getDirectory(true));
    }

    /**
     * @group Laminas-11258
     */
    public function testLaminas11258()
    {
        $validator = new File\NotExists();
        $this->assertFalse($validator->isValid(__DIR__ . '/_files/testsize.mo'));
        $this->assertArrayHasKey('fileNotExistsDoesExist', $validator->getMessages());
        $this->assertStringContainsString('File exists', current($validator->getMessages()));
    }

    public function testIsValidShouldThrowInvalidArgumentExceptionForArrayNotInFilesFormat()
    {
        $validator = new File\NotExists();
        $value     = ['foo' => 'bar'];
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Value array must be in $_FILES format');
        $validator->isValid($value);
    }

    public function invalidDirectoryArguments()
    {
        return [
            'null'       => [null],
            'true'       => [true],
            'false'      => [false],
            'zero'       => [0],
            'int'        => [1],
            'zero-float' => [0.0],
            'float'      => [1.1],
            'object'     => [(object) []],
        ];
    }

    /**
     * @dataProvider invalidDirectoryArguments
     */
    public function testAddingDirectoryUsingInvalidTypeRaisesException($value)
    {
        $validator = new File\NotExists();
        $this->expectException(InvalidArgumentException::class);
        $validator->addDirectory($value);
    }
}
