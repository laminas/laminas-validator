<?php

declare(strict_types=1);

namespace LaminasTest\Validator\File;

use Laminas\Validator\File\IsImage;
use Laminas\Validator\File\MimeType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

use function basename;
use function current;

/** @psalm-import-type OptionsArgument from MimeType */
final class IsImageTest extends TestCase
{
    /**
     * @psalm-return list<array{0: OptionsArgument, 1: mixed, 2: bool}>
     */
    public static function basicBehaviorDataProvider(): array
    {
        $testFile   = __DIR__ . '/_files/picture.jpg';
        $fileUpload = [
            'tmp_name' => $testFile,
            'name'     => basename($testFile),
            'size'     => 200,
            'error'    => 0,
            'type'     => 'image/jpeg',
        ];

        return [
            //    Options, isValid Param, Expected value
            [[], $fileUpload, true],
            [['mimeType' => 'jpeg'], $fileUpload, true],
            [['mimeType' => 'test/notype'], $fileUpload, false],
            [['mimeType' => 'image/gif, image/jpeg'], $fileUpload, true],
            [['mimeType' => ['image/vasa', 'image/jpeg']], $fileUpload, true],
            [['mimeType' => ['image/jpeg', 'gif']], $fileUpload, true],
            [['mimeType' => ['image/gif', 'gif']], $fileUpload, false],
            [['mimeType' => 'image/jp'], $fileUpload, false],
            [['mimeType' => 'image/jpg2000'], $fileUpload, false],
            [['mimeType' => 'image/jpeg2000'], $fileUpload, false],
        ];
    }

    /**
     * Ensures that the validator follows expected behavior
     *
     * @param OptionsArgument $options
     */
    #[DataProvider('basicBehaviorDataProvider')]
    public function testBasic(array $options, mixed $isValidParam, bool $expected): void
    {
        $validator = new IsImage($options);

        self::assertSame($expected, $validator->isValid($isValidParam));
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

        $validator = new IsImage(['mimeType' => 'test/notype']);

        self::assertFalse($validator->isValid($files));
        self::assertArrayHasKey(IsImage::FALSE_TYPE, $validator->getMessages());
    }

    public function testNonMimeOptionsAtConstructorStillSetsDefaults(): void
    {
        $validator = new IsImage([]);

        self::assertTrue($validator->isValid(__DIR__ . '/_files/picture.jpg'));
    }

    #[Group('Laminas-11258')]
    public function testLaminas11258(): void
    {
        $validator = new IsImage();

        self::assertFalse($validator->isValid(__DIR__ . '/_files/nofile.mo'));
        self::assertArrayHasKey('fileIsImageNotReadable', $validator->getMessages());
        self::assertStringContainsString('does not exist', current($validator->getMessages()));
    }
}
