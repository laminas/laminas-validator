<?php

declare(strict_types=1);

namespace LaminasTest\Validator\File;

use Laminas\Validator\File\ExcludeMimeType;
use Laminas\Validator\File\MimeType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use function basename;
use function chmod;
use function touch;
use function unlink;

use const UPLOAD_ERR_NO_FILE;

/** @psalm-import-type OptionsArgument from MimeType */
final class ExcludeMimeTypeTest extends TestCase
{
    /**
     * @psalm-return array<array-key, array{
     *     0: OptionsArgument,
     *     1: mixed,
     *     2: bool,
     *     3: array<string, string>
     * }>
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

        $falseTypeMessage = [ExcludeMimeType::FALSE_TYPE => "File has an incorrect mimetype of 'image/jpeg'"];

        return [
            //    Options, isValid Param, Expected value, messages
            [['mimeType' => 'image/gif'], $fileUpload, true, []],
            [['mimeType' => 'image'], $fileUpload, false, $falseTypeMessage],
            [['mimeType' => 'test/notype'], $fileUpload, true, []],
            [['mimeType' => 'image/gif, image/jpeg'], $fileUpload, false, $falseTypeMessage],
            [['mimeType' => ['image/vasa', 'image/gif']], $fileUpload, true, []],
            [['mimeType' => ['image/gif', 'jpeg']], $fileUpload, false, $falseTypeMessage],
            [['mimeType' => ['image/gif', 'gif']], $fileUpload, true, []],
        ];
    }

    /**
     * Ensures that the validator follows expected behavior
     *
     * @param OptionsArgument $options
     */
    #[DataProvider('basicBehaviorDataProvider')]
    public function testBasic(array $options, mixed $isValidParam, bool $expected, array $messages): void
    {
        $validator = new ExcludeMimeType($options);

        self::assertSame($expected, $validator->isValid($isValidParam));
        self::assertSame($messages, $validator->getMessages());
    }

    public function testEmptyFileShouldReturnFalseAndDisplayNotFoundMessage(): void
    {
        $validator = new ExcludeMimeType(['mimeType' => 'image/gif']);

        self::assertFalse($validator->isValid(''));
        self::assertArrayHasKey(ExcludeMimeType::NOT_READABLE, $validator->getMessages());
        self::assertNotEmpty($validator->getMessages()[ExcludeMimeType::NOT_READABLE]);
    }

    public function testEmptyArrayFileShouldReturnFalseAdnDisplayNotFoundMessage(): void
    {
        $validator = new ExcludeMimeType(['mimeType' => 'image/gif']);

        $filesArray = [
            'name'     => '',
            'size'     => 0,
            'tmp_name' => '',
            'error'    => UPLOAD_ERR_NO_FILE,
            'type'     => '',
        ];

        self::assertFalse($validator->isValid($filesArray));
        self::assertArrayHasKey(ExcludeMimeType::NOT_READABLE, $validator->getMessages());
        self::assertNotEmpty($validator->getMessages()[ExcludeMimeType::NOT_READABLE]);
    }

    public function testIsValidRaisesExceptionWithArrayNotInFilesFormat(): void
    {
        $validator = new ExcludeMimeType(['mimeType' => 'image/gif']);
        $value     = ['foo' => 'bar'];

        self::assertFalse($validator->isValid($value));
        $messages = $validator->getMessages();
        self::assertArrayHasKey(ExcludeMimeType::NOT_READABLE, $messages);
    }

    public function testUnreadableFile(): void
    {
        $validator = new ExcludeMimeType(['mimeType' => 'text/plain']);

        $path = __DIR__ . '/_files/no-read.txt';
        touch($path);
        chmod($path, 0333);
        try {
            self::assertFalse($validator->isValid($path));
            $messages = $validator->getMessages();
            self::assertArrayHasKey(ExcludeMimeType::NOT_READABLE, $messages);
        } finally {
            unlink($path);
        }
    }
}
