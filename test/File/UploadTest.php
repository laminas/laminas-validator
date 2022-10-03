<?php

declare(strict_types=1);

namespace LaminasTest\Validator\File;

use Laminas\Validator\Exception\InvalidArgumentException;
use Laminas\Validator\File\Upload;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\UploadedFileInterface;

use function current;

/**
 * @group Laminas_Validator
 * @covers \Laminas\Validator\File\Upload
 */
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
     *     0: array<string, UploadedFileInterface>,
     *     1: string,
     *     2: string
     * }>
     */
    public function invalidPsr7UploadedFiles(): iterable
    {
        $uploads = [];

        $upload = $this->createMock(UploadedFileInterface::class);
        $upload
            ->expects(self::any())
            ->method('getClientFilename')
            ->willReturn('test2');

        $upload
            ->expects(self::once())
            ->method('getError')
            ->willReturn(1);

        yield 'size' => [['test2' => $upload], 'test2', 'fileUploadErrorIniSize'];

        $uploads['test2'] = $upload;

        $upload = $this->createMock(UploadedFileInterface::class);
        $upload
            ->expects(self::any())
            ->method('getClientFilename')
            ->willReturn('test3');

        $upload
            ->expects(self::once())
            ->method('getError')
            ->willReturn(2);

        yield 'form-size' => [['test3' => $upload], 'test3', 'fileUploadErrorFormSize'];

        $uploads['test3'] = $upload;

        $upload = $this->createMock(UploadedFileInterface::class);
        $upload
            ->expects(self::any())
            ->method('getClientFilename')
            ->willReturn('test4');

        $upload
            ->expects(self::once())
            ->method('getError')
            ->willReturn(3);

        yield 'partial' => [['test4' => $upload], 'test4', 'fileUploadErrorPartial'];

        $uploads['test4'] = $upload;

        $upload = $this->createMock(UploadedFileInterface::class);
        $upload
            ->expects(self::any())
            ->method('getClientFilename')
            ->willReturn('test5');

        $upload
            ->expects(self::once())
            ->method('getError')
            ->willReturn(4);

        yield 'no-file' => [['test5' => $upload], 'test5', 'fileUploadErrorNoFile'];

        $uploads['test5'] = $upload;

        $upload = $this->createMock(UploadedFileInterface::class);
        $upload
            ->expects(self::any())
            ->method('getClientFilename')
            ->willReturn('test6');

        $upload
            ->expects(self::once())
            ->method('getError')
            ->willReturn(5);

        yield 'unknown' => [['test6' => $upload], 'test6', 'fileUploadErrorUnknown'];

        $uploads['test6'] = $upload;

        $upload = $this->createMock(UploadedFileInterface::class);
        $upload
            ->expects(self::any())
            ->method('getClientFilename')
            ->willReturn('test7');

        $upload
            ->expects(self::once())
            ->method('getError')
            ->willReturn(6);

        yield 'no-tmp-dir' => [['test7' => $upload], 'test7', 'fileUploadErrorNoTmpDir'];

        $uploads['test7'] = $upload;

        $upload = $this->createMock(UploadedFileInterface::class);
        $upload
            ->expects(self::any())
            ->method('getClientFilename')
            ->willReturn('test8');

        $upload
            ->expects(self::once())
            ->method('getError')
            ->willReturn(7);

        yield 'cannot write' => [['test8' => $upload], 'test8', 'fileUploadErrorCantWrite'];

        $uploads['test8'] = $upload;

        $upload = $this->createMock(UploadedFileInterface::class);
        $upload
            ->expects(self::any())
            ->method('getClientFilename')
            ->willReturn('test9');

        $upload
            ->expects(self::once())
            ->method('getError')
            ->willReturn(8);

        yield 'extension' => [['test9' => $upload], 'test9', 'fileUploadErrorExtension'];

        $uploads['test9'] = $upload;

        yield 'not-found' => [$uploads, 'test000', 'fileUploadErrorFileNotFound'];
    }

    /**
     * Validate invalid PSR-7 file uploads
     *
     * Not testing lookup by temp file name since PSR does not expose it.
     *
     * @dataProvider invalidPsr7UploadedFiles
     * @param UploadedFileInterface[] $files
     * @param string $fileName
     * @param string $expectedErrorKey
     * @return void
     */
    public function testRaisesExpectedErrorsForInvalidPsr7UploadedFileInput($files, $fileName, $expectedErrorKey)
    {
        $validator = new Upload();
        $validator->setFiles($files);

        self::assertFalse($validator->isValid($fileName));
        self::assertArrayHasKey($expectedErrorKey, $validator->getMessages());
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

    /**
     * @depends testGetFilesReturnsArtifactsFromPsr7UploadedFiles
     */
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

    /**
     * @group Laminas-10738
     */
    public function testGetFilesReturnsEmptyArrayWhenFilesSuperglobalIsNull(): void
    {
        $_FILES    = null;
        $validator = new Upload();
        $validator->setFiles();

        self::assertSame([], $validator->getFiles());
    }

    /**
     * @group Laminas-10738
     */
    public function testGetFilesReturnsEmptyArrayAfterSetFilesIsCalledWithNull(): void
    {
        $validator = new Upload();
        $validator->setFiles(null);

        self::assertSame([], $validator->getFiles());
    }

    /**
     * @group Laminas-11258
     */
    public function testLaminas11258(): void
    {
        $validator = new Upload();

        self::assertFalse($validator->isValid(__DIR__ . '/_files/nofile.mo'));
        self::assertArrayHasKey('fileUploadErrorFileNotFound', $validator->getMessages());
        self::assertStringContainsString("nofile.mo'", current($validator->getMessages()));
    }

    /**
     * @group Laminas-12128
     */
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
