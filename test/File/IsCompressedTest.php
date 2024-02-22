<?php

declare(strict_types=1);

namespace LaminasTest\Validator\File;

use Laminas\Validator\File\IsCompressed;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

use function basename;
use function current;
use function extension_loaded;
use function finfo_file;
use function finfo_open;
use function in_array;
use function is_array;

use const FILEINFO_MIME_TYPE;
use const PHP_VERSION_ID;

final class IsCompressedTest extends TestCase
{
    protected function getMagicMime(): string
    {
        return __DIR__ . '/_files/magic.7.mime';
    }

    /**
     * @psalm-return array<array-key, array{
     *     0: null|string|string[],
     *     1: array{
     *         tmp_name: string,
     *         name: string,
     *         size: int,
     *         error: int,
     *         type: string
     *     },
     *     2: bool
     * }>
     */
    public static function basicBehaviorDataProvider(): array
    {
        $testFile = __DIR__ . '/_files/test.zip';

        // Sometimes finfo gives application/zip and sometimes
        // application/x-zip ...
        $expectedMimeType = finfo_file(finfo_open(FILEINFO_MIME_TYPE), $testFile);

        $allowed    = ['application/zip', 'application/x-zip'];
        $fileUpload = [
            'tmp_name' => $testFile,
            'name'     => basename($testFile),
            'size'     => 200,
            'error'    => 0,
            'type'     => in_array($expectedMimeType, $allowed) ? $expectedMimeType : 'application/zip',
        ];

        return [
            //    Options, isValid Param, Expected value
            [null,                                                               $fileUpload, true],
            ['zip',                                                              $fileUpload, true],
            ['test/notype',                                                      $fileUpload, false],
            ['application/x-zip, application/zip, application/x-tar',            $fileUpload, true],
            [['application/x-zip', 'application/zip', 'application/x-tar'], $fileUpload, true],
            [['zip', 'tar'], $fileUpload, true],
            [['tar', 'arj'], $fileUpload, false],
        ];
    }

    /**
     * Skip a test if the file info extension is missing
     */
    protected function skipIfNoFileInfoExtension(): void
    {
        if (! extension_loaded('fileinfo')) {
            self::markTestSkipped(
                'This PHP Version has no finfo extension'
            );
        }
    }

    /**
     * Skip a test if finfo returns buggy information
     *
     * @param null|string|string[] $options
     */
    protected function skipIfBuggyMimeContentType($options): void
    {
        if (! is_array($options)) {
            $options = (array) $options;
        }

        if (! in_array('application/zip', $options)) {
            // finfo does not play a role; no need to skip
            return;
        }

        // Sometimes finfo gives application/zip and sometimes
        // application/x-zip ...
        $expectedMimeType = finfo_file(finfo_open(FILEINFO_MIME_TYPE), __DIR__ . '/_files/test.zip');

        if (! in_array($expectedMimeType, ['application/zip', 'application/x-zip'])) {
            self::markTestSkipped('finfo exhibits buggy behavior on this system!');
        }
    }

    /**
     * Ensures that the validator follows expected behavior
     *
     * @param null|string|string[] $options
     * @psalm-param array<string, string|int> $isValidParam
     */
    #[DataProvider('basicBehaviorDataProvider')]
    public function testBasic($options, array $isValidParam, bool $expected): void
    {
        $this->skipIfNoFileInfoExtension();
        $this->skipIfBuggyMimeContentType($options);

        $validator = new IsCompressed($options);
        $validator->enableHeaderCheck();

        self::assertSame($expected, $validator->isValid($isValidParam));
    }

    /**
     * Ensures that the validator follows expected behavior for legacy Laminas\Transfer API
     *
     * @param null|string|string[] $options
     * @psalm-param array<string, string|int> $isValidParam
     */
    #[DataProvider('basicBehaviorDataProvider')]
    public function testLegacy($options, array $isValidParam, bool $expected): void
    {
        $this->skipIfNoFileInfoExtension();
        $this->skipIfBuggyMimeContentType($options);

        $validator = new IsCompressed($options);
        $validator->enableHeaderCheck();

        self::assertSame($expected, $validator->isValid($isValidParam['tmp_name'], $isValidParam));
    }

    /** @psalm-return array<array{string|string[], string|string[], bool}> */
    public static function getMimeTypeProvider(): array
    {
        return [
            ['image/gif', 'image/gif', false],
            [['image/gif', 'video', 'text/test'], 'image/gif,video,text/test', false],
            [['image/gif', 'video', 'text/test'], ['image/gif', 'video', 'text/test'], true],
        ];
    }

    /**
     * Ensures that getMimeType() returns expected value
     *
     * @param string|string[] $mimeType
     * @param string|string[] $expected
     */
    #[DataProvider('getMimeTypeProvider')]
    public function testGetMimeType($mimeType, $expected, bool $asArray): void
    {
        $validator = new IsCompressed($mimeType);

        self::assertSame($expected, $validator->getMimeType($asArray));
    }

    /** @psalm-return array<array{string|string[], string, string[]}> */
    public static function setMimeTypeProvider(): array
    {
        return [
            ['image/jpeg', 'image/jpeg', ['image/jpeg']],
            ['image/gif, text/test', 'image/gif,text/test', ['image/gif', 'text/test']],
            [['video/mpeg', 'gif'], 'video/mpeg,gif', ['video/mpeg', 'gif']],
        ];
    }

    /**
     * Ensures that setMimeType() returns expected value
     *
     * @param string|list<string> $mimeType
     * @param list<string> $expectedAsArray
     */
    #[DataProvider('setMimeTypeProvider')]
    public function testSetMimeType(string|array $mimeType, string $expected, array $expectedAsArray): void
    {
        $validator = new IsCompressed('image/gif');
        $validator->setMimeType($mimeType);

        self::assertSame($expected, $validator->getMimeType());
        self::assertSame($expectedAsArray, $validator->getMimeType(true));
    }

    /**
     * Ensures that addMimeType() returns expected value
     */
    public function testAddMimeType(): void
    {
        $validator = new IsCompressed('image/gif');
        $validator->addMimeType('text');

        self::assertSame('image/gif,text', $validator->getMimeType());
        self::assertSame(['image/gif', 'text'], $validator->getMimeType(true));

        $validator->addMimeType('jpg, to');

        self::assertSame('image/gif,text,jpg,to', $validator->getMimeType());
        self::assertSame(['image/gif', 'text', 'jpg', 'to'], $validator->getMimeType(true));

        $validator->addMimeType(['zip', 'ti']);

        self::assertSame('image/gif,text,jpg,to,zip,ti', $validator->getMimeType());
        self::assertSame(['image/gif', 'text', 'jpg', 'to', 'zip', 'ti'], $validator->getMimeType(true));

        $validator->addMimeType('');

        self::assertSame('image/gif,text,jpg,to,zip,ti', $validator->getMimeType());
        self::assertSame(['image/gif', 'text', 'jpg', 'to', 'zip', 'ti'], $validator->getMimeType(true));
    }

    /**
     * @Laminas-8111
     */
    public function testErrorMessages(): void
    {
        $files = [
            'name'     => 'picture.jpg',
            'type'     => 'image/jpeg',
            'size'     => 200,
            'tmp_name' => __DIR__ . '/_files/picture.jpg',
            'error'    => 0,
        ];

        $validator = new IsCompressed('test/notype');
        $validator->enableHeaderCheck();

        self::assertFalse($validator->isValid(__DIR__ . '/_files/picture.jpg', $files));
        self::assertArrayHasKey('fileIsCompressedFalseType', $validator->getMessages());
    }

    /**
     * @todo Restore test branches under PHP 8.1 when https://bugs.php.net/bug.php?id=81426 is resolved
     */
    public function testOptionsAtConstructor(): void
    {
        if (! extension_loaded('fileinfo')) {
            self::markTestSkipped('This PHP Version has no finfo installed');
        }

        $magicFile = $this->getMagicMime();
        $options   = PHP_VERSION_ID >= 80100
            ? [
                'image/gif',
                'image/jpg',
                'enableHeaderCheck' => true,
            ]
            : [
                'image/gif',
                'image/jpg',
                'magicFile'         => $magicFile,
                'enableHeaderCheck' => true,
            ];
        $validator = new IsCompressed($options);

        if (PHP_VERSION_ID < 80100) {
            self::assertSame($magicFile, $validator->getMagicFile());
        }

        self::assertTrue($validator->getHeaderCheck());
        self::assertSame('image/gif,image/jpg', $validator->getMimeType());
    }

    public function testNonMimeOptionsAtConstructorStillSetsDefaults(): void
    {
        $validator = new IsCompressed([
            'enableHeaderCheck' => true,
        ]);

        self::assertNotEmpty($validator->getMimeType());
    }

    #[Group('Laminas-11258')]
    public function testLaminas11258(): void
    {
        $validator = new IsCompressed();

        self::assertFalse($validator->isValid(__DIR__ . '/_files/nofile.mo'));
        self::assertArrayHasKey('fileIsCompressedNotReadable', $validator->getMessages());
        self::assertStringContainsString('does not exist', current($validator->getMessages()));
    }
}
