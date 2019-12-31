<?php

/**
 * @see       https://github.com/laminas/laminas-validator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-validator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-validator/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Validator\File;

use Laminas\Validator\File;

/**
 * @group      Laminas_Validator
 */
class UploadFileTest extends \PHPUnit_Framework_TestCase
{
    public function uploadErrorsTestDataProvider()
    {
        $data = array();
        $errorTypes = array(
            0 => 'fileUploadFileErrorAttack',
            1 => 'fileUploadFileErrorIniSize',
            2 => 'fileUploadFileErrorFormSize',
            3 => 'fileUploadFileErrorPartial',
            4 => 'fileUploadFileErrorNoFile',
            5 => 'fileUploadFileErrorUnknown',
            6 => 'fileUploadFileErrorNoTmpDir',
            7 => 'fileUploadFileErrorCantWrite',
            8 => 'fileUploadFileErrorExtension',
            9 => 'fileUploadFileErrorUnknown',
        );
        $testSizeFile = __DIR__ . '/_files/testsize.mo';

        foreach ($errorTypes as $errorCode => $errorType) {
            $data[] = array(
                // fileInfo
                array(
                    'name'     => 'test' . $errorCode,
                    'type'     => 'text',
                    'size'     => 200 + $errorCode,
                    'tmp_name' => $testSizeFile,
                    'error'    => $errorCode,
                ),
                // messageKey
                $errorType,
            );
        }
        return $data;
    }

    /**
     * Ensures that the validator follows expected behavior
     *
     * @dataProvider uploadErrorsTestDataProvider
     * @return void
     */
    public function testBasic($fileInfo, $messageKey)
    {
        $validator = new File\UploadFile();
        $this->assertFalse($validator->isValid($fileInfo));
        $this->assertArrayHasKey($messageKey, $validator->getMessages());
    }

    /**
     * @return void
     */
    public function testRaisesExceptionWhenValueArrayIsBad()
    {
        $validator = new File\UploadFile();
        $this->setExpectedException('Laminas\Validator\Exception\InvalidArgumentException', '$_FILES format');
        $validator->isValid(array('foo', 'bar'));
    }

    /**
     * @group Laminas-11258
     */
    public function testLaminas11258()
    {
        $validator = new File\UploadFile();
        $this->assertFalse($validator->isValid(__DIR__ . '/_files/nofile.mo'));
        $this->assertArrayHasKey('fileUploadFileErrorFileNotFound', $validator->getMessages());
        $this->assertContains("not found", current($validator->getMessages()));
    }

    public function testEmptyFileShouldReturnFalseAndDisplayNotFoundMessage()
    {
        $validator = new File\UploadFile();

        $this->assertFalse($validator->isValid(''));
        $this->assertArrayHasKey(File\UploadFile::FILE_NOT_FOUND, $validator->getMessages());
    }

    public function testUploadErrorCodeShouldPrecedeEmptyFileCheck()
    {
        $validator = new File\UploadFile();

        $filesArray = array(
            'name'      => '',
            'size'      => 0,
            'tmp_name'  => '',
            'error'     => UPLOAD_ERR_NO_FILE,
            'type'      => '',
        );

        $this->assertFalse($validator->isValid($filesArray));
        $this->assertArrayHasKey(File\UploadFile::NO_FILE, $validator->getMessages());
        $this->assertArrayNotHasKey(File\UploadFile::FILE_NOT_FOUND, $validator->getMessages());
    }
}
