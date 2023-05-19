<?php

declare(strict_types=1);

namespace LaminasTest\Validator\File;

use Laminas\Validator\Exception\InvalidArgumentException;
use Laminas\Validator\File\ExcludeMimeType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use function basename;

use const UPLOAD_ERR_NO_FILE;

final class ExcludeMimeTypeTest extends TestCase
{
    /**
     * @psalm-return array<array-key, array{
     *     0: string|string[],
     *     1: array{
     *         tmp_name: string,
     *         name: string,
     *         size: int,
     *         error: int,
     *         type: string
     *     },
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
            ['image/gif', $fileUpload, true, []],
            ['image',                     $fileUpload, false, $falseTypeMessage],
            ['test/notype', $fileUpload, true, []],
            ['image/gif, image/jpeg',     $fileUpload, false, $falseTypeMessage],
            [['image/vasa', 'image/gif'], $fileUpload, true, []],
            [['image/gif', 'jpeg'], $fileUpload, false, $falseTypeMessage],
            [['image/gif', 'gif'], $fileUpload, true, []],
        ];
    }

    /**
     * Ensures that the validator follows expected behavior
     *
     * @param string|string[] $options
     */
    #[DataProvider('basicBehaviorDataProvider')]
    public function testBasic($options, array $isValidParam, bool $expected, array $messages): void
    {
        $validator = new ExcludeMimeType($options);
        $validator->enableHeaderCheck();

        self::assertSame($expected, $validator->isValid($isValidParam));
        self::assertSame($messages, $validator->getMessages());
    }

    /**
     * Ensures that the validator follows expected behavior for legacy Laminas\Transfer API
     *
     * @param string|string[] $options
     */
    #[DataProvider('basicBehaviorDataProvider')]
    public function testLegacy($options, array $isValidParam, bool $expected): void
    {
        $validator = new ExcludeMimeType($options);
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
        $validator = new ExcludeMimeType($mimeType);

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
     * @param string|string[] $mimeType
     * @param string[] $expectedAsArray
     */
    #[DataProvider('setMimeTypeProvider')]
    public function testSetMimeType($mimeType, string $expected, array $expectedAsArray): void
    {
        $validator = new ExcludeMimeType('image/gif');
        $validator->setMimeType($mimeType);

        self::assertSame($expected, $validator->getMimeType());
        self::assertSame($expectedAsArray, $validator->getMimeType(true));
    }

    /**
     * Ensures that addMimeType() returns expected value
     */
    public function testAddMimeType(): void
    {
        $validator = new ExcludeMimeType('image/gif');
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

    public function testEmptyFileShouldReturnFalseAndDisplayNotFoundMessage(): void
    {
        $validator = new ExcludeMimeType();

        self::assertFalse($validator->isValid(''));
        self::assertArrayHasKey(ExcludeMimeType::NOT_READABLE, $validator->getMessages());
        self::assertNotEmpty($validator->getMessages()[ExcludeMimeType::NOT_READABLE]);
    }

    public function testEmptyArrayFileShouldReturnFalseAdnDisplayNotFoundMessage(): void
    {
        $validator = new ExcludeMimeType();

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
        $validator = new ExcludeMimeType('image\gif');
        $value     = ['foo' => 'bar'];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Value array must be in $_FILES format');

        $validator->isValid($value);
    }
}
