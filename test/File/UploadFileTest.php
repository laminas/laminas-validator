<?php

namespace LaminasTest\Validator\File;

use Laminas\Validator\Exception\InvalidArgumentException;
use Laminas\Validator\File;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\UploadedFileInterface;

use function current;
use function sprintf;

use const UPLOAD_ERR_NO_FILE;
use const UPLOAD_ERR_OK;

/**
 * @group      Laminas_Validator
 */
class UploadFileTest extends TestCase
{
    /**
     * @psalm-return array<string, array{
     *     0: UploadedFileInterface|array<string, mixed>,
     *     1: string
     * }>
     */
    public function uploadErrorsTestDataProvider(): array
    {
        $data         = [];
        $errorTypes   = [
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
        ];
        $testSizeFile = __DIR__ . '/_files/testsize.mo';

        foreach ($errorTypes as $errorCode => $errorType) {
            $name        = sprintf('SAPI - %s', $errorType);
            $data[$name] = [
                // fileInfo
                [
                    'name'     => 'test' . $errorCode,
                    'type'     => 'text',
                    'size'     => 200 + $errorCode,
                    'tmp_name' => $testSizeFile,
                    'error'    => $errorCode,
                ],
                // messageKey
                $errorType,
            ];
        }

        // Diactoros does not have UNKNOWN error type.
        unset($errorTypes[9]);
        foreach ($errorTypes as $errorCode => $errorType) {
            if ($errorCode === UPLOAD_ERR_OK) {
                // Unable to get to this vector
                continue;
            }

            $name   = sprintf('PSR-7 - %s', $errorType);
            $upload = $this->prophesize(UploadedFileInterface::class);
            $upload->getClientFilename()->willReturn('test' . $errorCode);
            $upload->getError()->willReturn($errorCode);

            $data[$name] = [$upload->reveal(), $errorType];
        }

        return $data;
    }

    /**
     * Ensures that the validator follows expected behavior
     *
     * @dataProvider uploadErrorsTestDataProvider
     * @param array|UploadedFileInterface $fileInfo
     */
    public function testBasic($fileInfo, string $messageKey): void
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
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('$_FILES format');
        $validator->isValid(['foo', 'bar']);
    }

    /**
     * @group Laminas-11258
     */
    public function testLaminas11258(): void
    {
        $validator = new File\UploadFile();
        $this->assertFalse($validator->isValid(__DIR__ . '/_files/nofile.mo'));
        $this->assertArrayHasKey('fileUploadFileErrorFileNotFound', $validator->getMessages());
        $this->assertStringContainsString('not found', current($validator->getMessages()));
    }

    public function testEmptyFileShouldReturnFalseAndDisplayNotFoundMessage(): void
    {
        $validator = new File\UploadFile();

        $this->assertFalse($validator->isValid(''));
        $this->assertArrayHasKey(File\UploadFile::FILE_NOT_FOUND, $validator->getMessages());
    }

    public function testUploadErrorCodeShouldPrecedeEmptyFileCheck(): void
    {
        $validator = new File\UploadFile();

        $filesArray = [
            'name'     => '',
            'size'     => 0,
            'tmp_name' => '',
            'error'    => UPLOAD_ERR_NO_FILE,
            'type'     => '',
        ];

        $this->assertFalse($validator->isValid($filesArray));
        $this->assertArrayHasKey(File\UploadFile::NO_FILE, $validator->getMessages());
        $this->assertArrayNotHasKey(File\UploadFile::FILE_NOT_FOUND, $validator->getMessages());
    }
}
