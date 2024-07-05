<?php

declare(strict_types=1);

namespace LaminasTest\Validator\File;

use Laminas\Validator\File\IsCompressed;
use Laminas\Validator\File\MimeType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

use function basename;
use function current;
use function finfo_file;
use function finfo_open;
use function in_array;
use function is_array;

use const FILEINFO_MIME_TYPE;

/** @psalm-import-type OptionsArgument from MimeType */
final class IsCompressedTest extends TestCase
{
    /**
     * @psalm-return array<array-key, array{
     *     0: OptionsArgument,
     *     1: mixed,
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
            [[], $fileUpload, true],
            [['mimeType' => 'zip'], $fileUpload, true],
            [['mimeType' => 'test/notype'], $fileUpload, false],
            [['mimeType' => 'application/x-zip, application/zip, application/x-tar'], $fileUpload, true],
            [['mimeType' => ['application/x-zip', 'application/zip', 'application/x-tar']], $fileUpload, true],
            [['mimeType' => ['zip', 'tar']], $fileUpload, true],
            [['mimeType' => ['tar', 'arj']], $fileUpload, false],
        ];
    }

    /**
     * Skip a test if finfo returns buggy information
     *
     * @param OptionsArgument $options
     */
    protected function skipIfBuggyMimeContentType(array $options): void
    {
        $mimeType = $options['mimeType'] ?? [];
        if (! is_array($mimeType)) {
            $mimeType = [$mimeType];
        }

        if (! in_array('application/zip', $mimeType)) {
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
     * @param OptionsArgument $options
     */
    #[DataProvider('basicBehaviorDataProvider')]
    public function testBasic($options, mixed $isValidParam, bool $expected): void
    {
        $this->skipIfBuggyMimeContentType($options);

        $validator = new IsCompressed($options);

        self::assertSame($expected, $validator->isValid($isValidParam));
    }

    /**
     * @Laminas-8111
     */
    public function testErrorMessages(): void
    {
        $validator = new IsCompressed([]);

        self::assertFalse($validator->isValid(__DIR__ . '/_files/picture.jpg'));
        self::assertArrayHasKey(IsCompressed::FALSE_TYPE, $validator->getMessages());
    }

    #[Group('Laminas-11258')]
    public function testLaminas11258(): void
    {
        $validator = new IsCompressed();

        self::assertFalse($validator->isValid(__DIR__ . '/_files/nofile.mo'));
        self::assertArrayHasKey(IsCompressed::NOT_READABLE, $validator->getMessages());
        self::assertStringContainsString('does not exist', current($validator->getMessages()));
    }
}
