<?php

declare(strict_types=1);

namespace LaminasTest\Validator\File;

use Laminas\Validator\Exception\InvalidArgumentException;
use Laminas\Validator\File\Upload;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\UploadedFileInterface;

use function current;

final class UploadTest extends TestCase
{
    /**
     * Ensures that the validator follows expected behavior
     */
    public function testBasic(): void
    {
        $_FILES = [
            'test'  => [
                'name'     => 'test1',
                'type'     => 'text',
                'size'     => 200,
                'tmp_name' => 'tmp_test1',
                'error'    => 0,
            ],
            'test2' => [
                'name'     => 'test2',
                'type'     => 'text2',
                'size'     => 202,
                'tmp_name' => 'tmp_test2',
                'error'    => 1,
            ],
            'test3' => [
                'name'     => 'test3',
                'type'     => 'text3',
                'size'     => 203,
                'tmp_name' => 'tmp_test3',
                'error'    => 2,
            ],
            'test4' => [
                'name'     => 'test4',
                'type'     => 'text4',
                'size'     => 204,
                'tmp_name' => 'tmp_test4',
                'error'    => 3,
            ],
            'test5' => [
                'name'     => 'test5',
                'type'     => 'text5',
                'size'     => 205,
                'tmp_name' => 'tmp_test5',
                'error'    => 4,
            ],
            'test6' => [
                'name'     => 'test6',
                'type'     => 'text6',
                'size'     => 206,
                'tmp_name' => 'tmp_test6',
                'error'    => 5,
            ],
            'test7' => [
                'name'     => 'test7',
                'type'     => 'text7',
                'size'     => 207,
                'tmp_name' => 'tmp_test7',
                'error'    => 6,
            ],
            'test8' => [
                'name'     => 'test8',
                'type'     => 'text8',
                'size'     => 208,
                'tmp_name' => 'tmp_test8',
                'error'    => 7,
            ],
            'test9' => [
                'name'     => 'test9',
                'type'     => 'text9',
                'size'     => 209,
                'tmp_name' => 'tmp_test9',
                'error'    => 8,
            ],
        ];

        $validator = new Upload();
        self::assertFalse($validator->isValid('test'));
        self::assertArrayHasKey('fileUploadErrorAttack', $validator->getMessages());

        $validator = new Upload();
        self::assertFalse($validator->isValid('test2'));
        self::assertArrayHasKey('fileUploadErrorIniSize', $validator->getMessages());

        $validator = new Upload();
        self::assertFalse($validator->isValid('test3'));
        self::assertArrayHasKey('fileUploadErrorFormSize', $validator->getMessages());

        $validator = new Upload();
        self::assertFalse($validator->isValid('test4'));
        self::assertArrayHasKey('fileUploadErrorPartial', $validator->getMessages());

        $validator = new Upload();
        self::assertFalse($validator->isValid('test5'));
        self::assertArrayHasKey('fileUploadErrorNoFile', $validator->getMessages());

        $validator = new Upload();
        self::assertFalse($validator->isValid('test6'));
        self::assertArrayHasKey('fileUploadErrorUnknown', $validator->getMessages());

        $validator = new Upload();
        self::assertFalse($validator->isValid('test7'));
        self::assertArrayHasKey('fileUploadErrorNoTmpDir', $validator->getMessages());

        $validator = new Upload();
        self::assertFalse($validator->isValid('test8'));
        self::assertArrayHasKey('fileUploadErrorCantWrite', $validator->getMessages());

        $validator = new Upload();
        self::assertFalse($validator->isValid('test9'));
        self::assertArrayHasKey('fileUploadErrorExtension', $validator->getMessages());

        $validator = new Upload();
        self::assertFalse($validator->isValid('test1'));
        self::assertArrayHasKey('fileUploadErrorAttack', $validator->getMessages());

        $validator = new Upload();
        self::assertFalse($validator->isValid('tmp_test1'));
        self::assertArrayHasKey('fileUploadErrorAttack', $validator->getMessages());

        $validator = new Upload();
        self::assertFalse($validator->isValid('test000'));
        self::assertArrayHasKey('fileUploadErrorFileNotFound', $validator->getMessages());
    }

    /**
     * @psalm-return iterable<string, array{
     *     0: int,
     *     1: string,
     *     2: string
     * }>
     */
    public static function invalidPsr7UploadedFiles(): iterable
    {
        yield 'size' => [1, 'test2', 'fileUploadErrorIniSize'];
        yield 'form-size' => [2, 'test3', 'fileUploadErrorFormSize'];
        yield 'partial' => [3, 'test4', 'fileUploadErrorPartial'];
        yield 'no-file' => [4, 'test5', 'fileUploadErrorNoFile'];
        yield 'unknown' => [5, 'test6', 'fileUploadErrorUnknown'];
        yield 'no-tmp-dir' => [6, 'test7', 'fileUploadErrorNoTmpDir'];
        yield 'cannot write' => [7, 'test8', 'fileUploadErrorCantWrite'];
        yield 'extension' => [8, 'test9', 'fileUploadErrorExtension'];
    }

    /**
     * Validate invalid PSR-7 file uploads
     *
     * Not testing lookup by temp file name since PSR does not expose it.
     */
    #[DataProvider('invalidPsr7UploadedFiles')]
    public function testRaisesExpectedErrorsForInvalidPsr7UploadedFileInput(
        int $errorCode,
        string $fileName,
        string $expectedErrorKey
    ): void {
        $upload = $this->createMock(UploadedFileInterface::class);
        $upload
            ->expects(self::any())
            ->method('getClientFilename')
            ->willReturn($fileName);

        $upload
            ->expects(self::once())
            ->method('getError')
            ->willReturn($errorCode);

        $validator = new Upload();
        $validator->setFiles([$fileName => $upload]);

        self::assertFalse($validator->isValid($fileName));
        self::assertArrayHasKey($expectedErrorKey, $validator->getMessages());
    }

    public function testFileNotFound(): void
    {
        $validator = new Upload();
        $validator->setFiles([]);

        self::assertFalse($validator->isValid('notThere'));
        self::assertArrayHasKey('fileUploadErrorFileNotFound', $validator->getMessages());
    }

    public function testCanValidateCorrectlyFormedPsr7UploadedFiles(): void
    {
        $upload = $this->createMock(UploadedFileInterface::class);
        $upload
            ->expects(self::exactly(2))
            ->method('getClientFilename')
            ->willReturn('test');

        $upload
            ->expects(self::once())
            ->method('getError')
            ->willReturn(0);

        $validator = new Upload();
        $validator->setFiles(['upload' => $upload]);

        self::assertTrue($validator->isValid('test'));
    }

    /**
     * Ensures that getFiles() returns expected value
     */
    public function testGetFiles(): void
    {
        $_FILES = [
            'test'  => [
                'name'     => 'test1',
                'type'     => 'text',
                'size'     => 200,
                'tmp_name' => 'tmp_test1',
                'error'    => 0,
            ],
            'test2' => [
                'name'     => 'test3',
                'type'     => 'text2',
                'size'     => 202,
                'tmp_name' => 'tmp_test2',
                'error'    => 1,
            ],
        ];

        $files = [
            'test' => [
                'name'     => 'test1',
                'type'     => 'text',
                'size'     => 200,
                'tmp_name' => 'tmp_test1',
                'error'    => 0,
            ],
        ];

        $files1 = [
            'test2' => [
                'name'     => 'test3',
                'type'     => 'text2',
                'size'     => 202,
                'tmp_name' => 'tmp_test2',
                'error'    => 1,
            ],
        ];

        $validator = new Upload();
        self::assertSame($files, $validator->getFiles('test'));
        self::assertSame($files, $validator->getFiles('test1'));
        self::assertSame($files1, $validator->getFiles('test3'));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('was not found');
        self::assertSame([], $validator->getFiles('test5'));
    }

    public function testGetFilesReturnsArtifactsFromPsr7UploadedFiles(): Upload
    {
        $upload1 = $this->createMock(UploadedFileInterface::class);
        $upload1
            ->expects(self::any())
            ->method('getClientFilename')
            ->willReturn('test1');

        $upload2 = $this->createMock(UploadedFileInterface::class);
        $upload2
            ->expects(self::any())
            ->method('getClientFilename')
            ->willReturn('test3');

        $files = [
            'test'  => $upload1,
            'test2' => $upload2,
        ];

        $validator = new Upload();
        $validator->setFiles($files);

        // Retrieve by index
        self::assertSame(['test' => $files['test']], $validator->getFiles('test'));
        self::assertSame(['test2' => $files['test2']], $validator->getFiles('test2'));

        // Retrieve by client filename
        self::assertSame(['test' => $files['test']], $validator->getFiles('test1'));
        self::assertSame(['test2' => $files['test2']], $validator->getFiles('test3'));

        return $validator;
    }

    #[Depends('testGetFilesReturnsArtifactsFromPsr7UploadedFiles')]
    public function testGetFilesRaisesExceptionWhenPsr7UploadedFilesArrayDoesNotContainGivenFilename(
        Upload $validator
    ): void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('was not found');

        $validator->getFiles('test5');
    }

    /**
     * Ensures that setFiles() returns expected value
     */
    public function testSetFiles(): void
    {
        $files = [
            'test'  => [
                'name'     => 'test1',
                'type'     => 'text',
                'size'     => 200,
                'tmp_name' => 'tmp_test1',
                'error'    => 0,
            ],
            'test2' => [
                'name'     => 'test2',
                'type'     => 'text2',
                'size'     => 202,
                'tmp_name' => 'tmp_test2',
                'error'    => 1,
            ],
        ];

        $_FILES = [
            'test' => [
                'name'     => 'test3',
                'type'     => 'text3',
                'size'     => 203,
                'tmp_name' => 'tmp_test3',
                'error'    => 2,
            ],
        ];

        $validator = new Upload();
        $validator->setFiles([]);

        self::assertSame($_FILES, $validator->getFiles());

        $validator->setFiles();

        self::assertSame($_FILES, $validator->getFiles());

        $validator->setFiles($files);

        self::assertSame($files, $validator->getFiles());
    }

    public function testCanPopulateFilesFromArrayOfPsr7UploadedFiles(): void
    {
        $upload1 = $this->createMock(UploadedFileInterface::class);
        $upload2 = $this->createMock(UploadedFileInterface::class);

        $psrFiles = [
            'test4' => $upload1,
            'test5' => $upload2,
        ];

        $validator = new Upload();
        $validator->setFiles($psrFiles);

        self::assertSame($psrFiles, $validator->getFiles());
    }

    #[Group('Laminas-10738')]
    public function testGetFilesReturnsEmptyArrayWhenFilesSuperglobalIsNull(): void
    {
        $_FILES    = null;
        $validator = new Upload();
        $validator->setFiles();

        self::assertSame([], $validator->getFiles());
    }

    #[Group('Laminas-10738')]
    public function testGetFilesReturnsEmptyArrayAfterSetFilesIsCalledWithNull(): void
    {
        $validator = new Upload();
        $validator->setFiles(null);

        self::assertSame([], $validator->getFiles());
    }

    #[Group('Laminas-11258')]
    public function testLaminas11258(): void
    {
        $validator = new Upload();

        self::assertFalse($validator->isValid(__DIR__ . '/_files/nofile.mo'));
        self::assertArrayHasKey('fileUploadErrorFileNotFound', $validator->getMessages());
        self::assertStringContainsString("nofile.mo'", current($validator->getMessages()));
    }

    #[Group('Laminas-12128')]
    public function testErrorMessage(): void
    {
        $_FILES = [
            'foo' => [
                'name'     => 'bar',
                'type'     => 'text',
                'size'     => 100,
                'tmp_name' => 'tmp_bar',
                'error'    => 7,
            ],
        ];

        $validator = new Upload();
        $validator->isValid('foo');

        self::assertSame(
            [
                'fileUploadErrorCantWrite' => "Failed to write file 'bar' to disk",
            ],
            $validator->getMessages()
        );
    }
}
