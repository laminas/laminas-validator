<?php

declare(strict_types=1);

namespace LaminasTest\Validator\File;

use Laminas\Validator\Exception\InvalidArgumentException;
use Laminas\Validator\File\UploadFile;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\UploadedFileInterface;

use function current;
use function is_int;
use function sprintf;

use const UPLOAD_ERR_NO_FILE;
use const UPLOAD_ERR_OK;

final class UploadFileTest extends TestCase
{
    private UploadFile $validator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = new UploadFile();
    }

    /**
     * @psalm-return array<string, array{
     *     0: int|array<string, mixed>,
     *     1: string
     * }>
     */
    public static function uploadErrorsTestDataProvider(): array
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

            $name = sprintf('PSR-7 - %s', $errorType);

            $data[$name] = [$errorCode, $errorType];
        }

        return $data;
    }

    /**
     * Ensures that the validator follows expected behavior
     */
    #[DataProvider('uploadErrorsTestDataProvider')]
    public function testBasic(array|int $fileInfo, string $messageKey): void
    {
        if (is_int($fileInfo)) {
            $errorCode = $fileInfo;
            $fileInfo  = $this->createMock(UploadedFileInterface::class);
            $fileInfo->expects(self::never())
                ->method('getClientFilename');

            $fileInfo->expects(self::once())
                ->method('getError')
                ->willReturn($errorCode);
        }

        self::assertFalse($this->validator->isValid($fileInfo));
        self::assertArrayHasKey($messageKey, $this->validator->getMessages());
    }

    public function testRaisesExceptionWhenValueArrayIsBad(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('$_FILES format');

        $this->validator->isValid(['foo', 'bar']);
    }

    #[Group('Laminas-11258')]
    public function testLaminas11258(): void
    {
        self::assertFalse($this->validator->isValid(__DIR__ . '/_files/nofile.mo'));
        self::assertArrayHasKey('fileUploadFileErrorFileNotFound', $this->validator->getMessages());
        self::assertStringContainsString('not found', current($this->validator->getMessages()));
    }

    public function testEmptyFileShouldReturnFalseAndDisplayNotFoundMessage(): void
    {
        self::assertFalse($this->validator->isValid(''));
        self::assertArrayHasKey(UploadFile::FILE_NOT_FOUND, $this->validator->getMessages());
    }

    public function testUploadErrorCodeShouldPrecedeEmptyFileCheck(): void
    {
        $filesArray = [
            'name'     => '',
            'size'     => 0,
            'tmp_name' => '',
            'error'    => UPLOAD_ERR_NO_FILE,
            'type'     => '',
        ];

        self::assertFalse($this->validator->isValid($filesArray));
        self::assertArrayHasKey(UploadFile::NO_FILE, $this->validator->getMessages());
        self::assertArrayNotHasKey(UploadFile::FILE_NOT_FOUND, $this->validator->getMessages());
    }
}
