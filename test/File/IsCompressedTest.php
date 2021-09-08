<?php

namespace LaminasTest\Validator\File;

use Laminas\Validator\File;
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

/**
 * IsCompressed testbed
 *
 * @group      Laminas_Validator
 */
class IsCompressedTest extends TestCase
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
    public function basicBehaviorDataProvider(): array
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
            $this->markTestSkipped(
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
            $this->markTestSkipped('finfo exhibits buggy behavior on this system!');
        }
    }

    /**
     * Ensures that the validator follows expected behavior
     *
     * @dataProvider basicBehaviorDataProvider
     * @param null|string|string[] $options
     * @param array $isValidParam
     */
    public function testBasic($options, $isValidParam, bool $expected): void
    {
        $this->skipIfNoFileInfoExtension();
        $this->skipIfBuggyMimeContentType($options);

        $validator = new File\IsCompressed($options);
        $validator->enableHeaderCheck();
        $this->assertEquals($expected, $validator->isValid($isValidParam));
    }

    /**
     * Ensures that the validator follows expected behavior for legacy Laminas\Transfer API
     *
     * @dataProvider basicBehaviorDataProvider
     * @param null|string|string[] $options
     * @param array $isValidParam
     */
    public function testLegacy($options, $isValidParam, bool $expected): void
    {
        if (! is_array($isValidParam)) {
            // nothing to test
            return;
        }

        $this->skipIfNoFileInfoExtension();
        $this->skipIfBuggyMimeContentType($options);

        $validator = new File\IsCompressed($options);
        $validator->enableHeaderCheck();
        $this->assertEquals($expected, $validator->isValid($isValidParam['tmp_name'], $isValidParam));
    }

    /**
     * Ensures that getMimeType() returns expected value
     *
     * @return void
     */
    public function testGetMimeType()
    {
        $validator = new File\IsCompressed('image/gif');
        $this->assertEquals('image/gif', $validator->getMimeType());

        $validator = new File\IsCompressed(['image/gif', 'video', 'text/test']);
        $this->assertEquals('image/gif,video,text/test', $validator->getMimeType());

        $validator = new File\IsCompressed(['image/gif', 'video', 'text/test']);
        $this->assertEquals(['image/gif', 'video', 'text/test'], $validator->getMimeType(true));
    }

    /**
     * Ensures that setMimeType() returns expected value
     *
     * @return void
     */
    public function testSetMimeType()
    {
        $validator = new File\IsCompressed('image/gif');
        $validator->setMimeType('image/jpeg');
        $this->assertEquals('image/jpeg', $validator->getMimeType());
        $this->assertEquals(['image/jpeg'], $validator->getMimeType(true));

        $validator->setMimeType('image/gif, text/test');
        $this->assertEquals('image/gif,text/test', $validator->getMimeType());
        $this->assertEquals(['image/gif', 'text/test'], $validator->getMimeType(true));

        $validator->setMimeType(['video/mpeg', 'gif']);
        $this->assertEquals('video/mpeg,gif', $validator->getMimeType());
        $this->assertEquals(['video/mpeg', 'gif'], $validator->getMimeType(true));
    }

    /**
     * Ensures that addMimeType() returns expected value
     *
     * @return void
     */
    public function testAddMimeType()
    {
        $validator = new File\IsCompressed('image/gif');
        $validator->addMimeType('text');
        $this->assertEquals('image/gif,text', $validator->getMimeType());
        $this->assertEquals(['image/gif', 'text'], $validator->getMimeType(true));

        $validator->addMimeType('jpg, to');
        $this->assertEquals('image/gif,text,jpg,to', $validator->getMimeType());
        $this->assertEquals(['image/gif', 'text', 'jpg', 'to'], $validator->getMimeType(true));

        $validator->addMimeType(['zip', 'ti']);
        $this->assertEquals('image/gif,text,jpg,to,zip,ti', $validator->getMimeType());
        $this->assertEquals(['image/gif', 'text', 'jpg', 'to', 'zip', 'ti'], $validator->getMimeType(true));

        $validator->addMimeType('');
        $this->assertEquals('image/gif,text,jpg,to,zip,ti', $validator->getMimeType());
        $this->assertEquals(['image/gif', 'text', 'jpg', 'to', 'zip', 'ti'], $validator->getMimeType(true));
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

        $validator = new File\IsCompressed('test/notype');
        $validator->enableHeaderCheck();
        $this->assertFalse($validator->isValid(__DIR__ . '/_files/picture.jpg', $files));
        $error = $validator->getMessages();
        $this->assertArrayHasKey('fileIsCompressedFalseType', $error);
    }

    /**
     * @todo Restore test branches under PHP 8.1 when https://bugs.php.net/bug.php?id=81426 is resolved
     */
    public function testOptionsAtConstructor(): void
    {
        if (! extension_loaded('fileinfo')) {
            $this->markTestSkipped('This PHP Version has no finfo installed');
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
        $validator = new File\IsCompressed($options);

        if (PHP_VERSION_ID < 80100) {
            $this->assertEquals($magicFile, $validator->getMagicFile());
        }

        $this->assertTrue($validator->getHeaderCheck());
        $this->assertEquals('image/gif,image/jpg', $validator->getMimeType());
    }

    public function testNonMimeOptionsAtConstructorStillSetsDefaults(): void
    {
        $validator = new File\IsCompressed([
            'enableHeaderCheck' => true,
        ]);

        $this->assertNotEmpty($validator->getMimeType());
    }

    /**
     * @group Laminas-11258
     */
    public function testLaminas11258(): void
    {
        $validator = new File\IsCompressed();
        $this->assertFalse($validator->isValid(__DIR__ . '/_files/nofile.mo'));
        $this->assertArrayHasKey('fileIsCompressedNotReadable', $validator->getMessages());
        $this->assertStringContainsString('does not exist', current($validator->getMessages()));
    }
}
