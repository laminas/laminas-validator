<?php

declare(strict_types=1);

namespace LaminasTest\Validator\File;

use LaminasTest\Validator\File\TestAsset\FileInformation;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;

use function basename;
use function mime_content_type;

/** @covers \Laminas\Validator\File\FileInformationTrait */
final class FileInformationTraitTest extends TestCase
{
    /** @var StreamInterface&MockObject */
    private StreamInterface $stream;

    /** @var UploadedFileInterface&MockObject */
    private UploadedFileInterface $upload;

    private string $testFile;

    private FileInformation $fileInformation;

    protected function setUp(): void
    {
        parent::setUp();

        $this->stream = $this->createMock(StreamInterface::class);
        $this->upload = $this->createMock(UploadedFileInterface::class);

        $this->testFile = __DIR__ . '/_files/testsize.mo';

        $this->fileInformation = new FileInformation();
    }

    public function testLegacyFileInfoBasic(): void
    {
        $basename = basename($this->testFile);
        $file     = [
            'name'     => $basename,
            'tmp_name' => $this->testFile,
        ];

        $fileInfo = $this->fileInformation->checkFileInformation(
            $basename,
            $file
        );

        self::assertSame([
            'filename' => $file['name'],
            'file'     => $file['tmp_name'],
        ], $fileInfo);
    }

    public function testLegacyFileInfoWithFiletype(): void
    {
        $basename = basename($this->testFile);
        $file     = [
            'name'     => $basename,
            'tmp_name' => $this->testFile,
            'type'     => 'mo',
        ];

        $fileInfo = $this->fileInformation->checkFileInformation(
            $basename,
            $file,
            true
        );

        self::assertSame([
            'filename' => $file['name'],
            'file'     => $file['tmp_name'],
            'filetype' => $file['type'],
        ], $fileInfo);
    }

    public function testLegacyFileInfoWithBasename(): void
    {
        $basename = basename($this->testFile);
        $file     = [
            'name'     => $basename,
            'tmp_name' => $this->testFile,
        ];

        $fileInfo = $this->fileInformation->checkFileInformation(
            $basename,
            $file,
            false,
            true
        );

        self::assertSame([
            'filename' => $file['name'],
            'file'     => $file['tmp_name'],
            'basename' => basename($file['tmp_name']),
        ], $fileInfo);
    }

    public function testSapiFileInfoBasic(): void
    {
        $file = [
            'name'     => basename($this->testFile),
            'tmp_name' => $this->testFile,
        ];

        $fileInfo = $this->fileInformation->checkFileInformation(
            $file
        );

        self::assertSame([
            'file'     => $file['tmp_name'],
            'filename' => $file['name'],
        ], $fileInfo);
    }

    public function testSapiFileInfoWithFiletype(): void
    {
        $file = [
            'name'     => basename($this->testFile),
            'tmp_name' => $this->testFile,
            'type'     => 'mo',
        ];

        $fileInfo = $this->fileInformation->checkFileInformation(
            $file,
            null,
            true
        );

        self::assertSame([
            'file'     => $file['tmp_name'],
            'filename' => $file['name'],
            'filetype' => $file['type'],
        ], $fileInfo);
    }

    public function testSapiFileInfoWithBasename(): void
    {
        $file = [
            'name'     => basename($this->testFile),
            'tmp_name' => $this->testFile,
        ];

        $fileInfo = $this->fileInformation->checkFileInformation(
            $file,
            null,
            false,
            true
        );

        self::assertSame([
            'file'     => $file['tmp_name'],
            'filename' => $file['name'],
            'basename' => basename($file['tmp_name']),
        ], $fileInfo);
    }

    public function testPsr7FileInfoBasic(): void
    {
        $this->stream
            ->expects(self::once())
            ->method('getMetadata')
            ->with('uri')
            ->willReturn($this->testFile);

        $this->upload
            ->expects(self::once())
            ->method('getClientFilename')
            ->willReturn(basename($this->testFile));

        $this->upload
            ->expects(self::never())
            ->method('getClientMediaType');

        $this->upload
            ->expects(self::once())
            ->method('getStream')
            ->willReturn($this->stream);

        $fileInfo = $this->fileInformation->checkFileInformation(
            $this->upload
        );

        self::assertSame([
            'file'     => $this->testFile,
            'filename' => basename($this->testFile),
        ], $fileInfo);
    }

    public function testPsr7FileInfoBasicWithFiletype(): void
    {
        $this->stream
            ->expects(self::once())
            ->method('getMetadata')
            ->with('uri')
            ->willReturn($this->testFile);

        $this->upload
            ->expects(self::once())
            ->method('getClientFilename')
            ->willReturn(basename($this->testFile));

        $this->upload
            ->expects(self::once())
            ->method('getClientMediaType')
            ->willReturn(mime_content_type($this->testFile));

        $this->upload
            ->expects(self::once())
            ->method('getStream')
            ->willReturn($this->stream);

        $fileInfo = $this->fileInformation->checkFileInformation(
            $this->upload,
            null,
            true
        );

        self::assertSame([
            'file'     => $this->testFile,
            'filename' => basename($this->testFile),
            'filetype' => mime_content_type($this->testFile),
        ], $fileInfo);
    }

    public function testPsr7FileInfoBasicWithBasename(): void
    {
        $this->stream
            ->expects(self::once())
            ->method('getMetadata')
            ->with('uri')
            ->willReturn($this->testFile);

        $this->upload
            ->expects(self::once())
            ->method('getClientFilename')
            ->willReturn(basename($this->testFile));

        $this->upload
            ->expects(self::never())
            ->method('getClientMediaType');

        $this->upload
            ->expects(self::once())
            ->method('getStream')
            ->willReturn($this->stream);

        $fileInfo = $this->fileInformation->checkFileInformation(
            $this->upload,
            null,
            false,
            true
        );

        self::assertSame([
            'file'     => $this->testFile,
            'filename' => basename($this->testFile),
            'basename' => basename($this->testFile),
        ], $fileInfo);
    }

    public function testFileBasedFileInfoBasic(): void
    {
        $fileInfo = $this->fileInformation->checkFileInformation(
            $this->testFile
        );

        self::assertSame([
            'file'     => $this->testFile,
            'filename' => basename($this->testFile),
        ], $fileInfo);
    }

    public function testFileBasedFileInfoBasicWithFiletype(): void
    {
        $fileInfo = $this->fileInformation->checkFileInformation(
            $this->testFile,
            null,
            true
        );

        self::assertSame([
            'file'     => $this->testFile,
            'filename' => basename($this->testFile),
            'filetype' => null,
        ], $fileInfo);
    }

    public function testFileBasedFileInfoBasicWithBasename(): void
    {
        $fileInfo = $this->fileInformation->checkFileInformation(
            $this->testFile,
            null,
            false,
            true
        );

        self::assertSame([
            'file'     => $this->testFile,
            'filename' => basename($this->testFile),
            'basename' => basename($this->testFile),
        ], $fileInfo);
    }
}
