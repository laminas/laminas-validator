<?php

declare(strict_types=1);

namespace LaminasTest\Validator\File;

use Laminas\Validator\Exception\InvalidArgumentException;
use Laminas\Validator\Exception\InvalidMagicMimeFileException;
use Laminas\Validator\File\MimeType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

use function basename;
use function current;
use function extension_loaded;
use function getenv;
use function is_array;

use const UPLOAD_ERR_NO_FILE;

final class MimeTypeTest extends TestCase
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
     *     2: bool
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
            'type'     => 'image/jpg',
        ];

        return [
            //    Options, isValid Param, Expected value
            [['image/jpg', 'image/jpeg'], $fileUpload, true],
            ['image',                                   $fileUpload, true],
            ['test/notype',                             $fileUpload, false],
            ['image/gif, image/jpg, image/jpeg',        $fileUpload, true],
            [['image/vasa', 'image/jpg', 'image/jpeg'], $fileUpload, true],
            [['image/jpg', 'image/jpeg', 'gif'], $fileUpload, true],
            [['image/gif', 'gif'], $fileUpload, false],
            ['image/jp',                                $fileUpload, false],
            ['image/jpg2000',                           $fileUpload, false],
            ['image/jpeg2000',                          $fileUpload, false],
        ];
    }

    /**
     * Ensures that the validator follows expected behavior
     *
     * @param string|string[] $options
     * @param array $isValidParam
     */
    #[DataProvider('basicBehaviorDataProvider')]
    public function testBasic($options, $isValidParam, bool $expected): void
    {
        $validator = new MimeType($options);
        $validator->enableHeaderCheck();

        self::assertSame($expected, $validator->isValid($isValidParam));
    }

    /**
     * Ensures that the validator follows expected behavior for legacy Laminas\Transfer API
     *
     * @param string|string[] $options
     * @param array $isValidParam
     */
    #[DataProvider('basicBehaviorDataProvider')]
    public function testLegacy($options, $isValidParam, bool $expected): void
    {
        if (is_array($isValidParam)) {
            $validator = new MimeType($options);
            $validator->enableHeaderCheck();

            self::assertSame($expected, $validator->isValid($isValidParam['tmp_name'], $isValidParam));
        }
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
        $validator = new MimeType($mimeType);

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
        $validator = new MimeType('image/gif');
        $validator->setMimeType($mimeType);

        self::assertSame($expected, $validator->getMimeType());
        self::assertSame($expectedAsArray, $validator->getMimeType(true));
    }

    /**
     * Ensures that addMimeType() returns expected value
     */
    public function testAddMimeType(): void
    {
        $validator = new MimeType('image/gif');
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

    public function testSetAndGetMagicFile(): void
    {
        if (! extension_loaded('fileinfo')) {
            self::markTestSkipped('This PHP Version has no finfo installed');
        }

        $validator = new MimeType('image/gif');
        $magic     = getenv('magic');

        if ($magic !== false && $magic !== '') {
            $mimetype = $validator->getMagicFile();

            self::assertSame($magic, $mimetype);
        }

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('could not be');

        $validator->setMagicFile('/unknown/magic/file');
    }

    public function testSetMagicFileWithinConstructor(): void
    {
        if (! extension_loaded('fileinfo')) {
            self::markTestSkipped('This PHP Version has no finfo installed');
        }

        $this->expectException(InvalidMagicMimeFileException::class);
        $this->expectExceptionMessage('could not be used by ext/finfo');

        new MimeType(['image/gif', 'magicFile' => __FILE__]);
    }

    public function testOptionsAtConstructor(): void
    {
        $validator = new MimeType([
            'image/gif',
            'image/jpg',
            'enableHeaderCheck' => true,
        ]);

        self::assertTrue($validator->getHeaderCheck());
        self::assertSame('image/gif,image/jpg', $validator->getMimeType());
    }

    #[Group('Laminas-11258')]
    public function testLaminas11258(): void
    {
        $validator = new MimeType([
            'image/gif',
            'image/jpg',
            'headerCheck' => true,
        ]);

        self::assertFalse($validator->isValid(__DIR__ . '/_files/nofile.mo'));
        self::assertArrayHasKey('fileMimeTypeNotReadable', $validator->getMessages());
        self::assertStringContainsString('does not exist', current($validator->getMessages()));
    }

    public function testDisableMagicFile(): void
    {
        $validator = new MimeType('image/gif');
        $magic     = getenv('magic');

        if ($magic !== false && $magic !== '') {
            $mimetype = $validator->getMagicFile();

            self::assertSame($magic, $mimetype);
        }

        $validator->disableMagicFile(true);

        self::assertTrue($validator->isMagicFileDisabled());

        if ($magic !== false && $magic !== '') {
            $mimetype = $validator->getMagicFile();

            self::assertSame($magic, $mimetype);
        }
    }

    #[Group('Laminas-10461')]
    public function testDisablingMagicFileByConstructor(): void
    {
        $validator = new MimeType([
            'magicFile' => false,
        ]);

        self::assertFalse($validator->getMagicFile());
    }

    public function testEmptyFileShouldReturnFalseAndDisplayNotFoundMessage(): void
    {
        if (! extension_loaded('fileinfo')) {
            self::markTestSkipped('This PHP Version has no finfo installed');
        }

        $validator = new MimeType();

        self::assertFalse($validator->isValid(''));

        $filesArray = [
            'name'     => '',
            'size'     => 0,
            'tmp_name' => '',
            'error'    => UPLOAD_ERR_NO_FILE,
            'type'     => '',
        ];

        self::assertFalse($validator->isValid($filesArray));
    }

    public function testConstructorCanAcceptOptionsArray(): void
    {
        $mimeType  = 'image/gif';
        $options   = ['mimeType' => $mimeType];
        $validator = new MimeType($options);

        self::assertSame($mimeType, $validator->getMimeType());
    }

    public function testSettingMagicFileWithEmptyArrayNullifiesValue(): void
    {
        $validator = new MimeType();
        $validator->setMagicFile([]);

        $r = new ReflectionProperty($validator, 'options');

        $options = $r->getValue($validator);

        self::assertIsArray($options);
        self::assertNull($options['magicFile']);
    }

    /**
     * @psalm-return array<string, array{scalar|object|null}>
     */
    public static function invalidMimeTypeTypes(): array
    {
        return [
            'null'       => [null],
            'true'       => [true],
            'false'      => [false],
            'zero'       => [0],
            'int'        => [1],
            'zero-float' => [0.0],
            'float'      => [1.1],
            'object'     => [(object) []],
        ];
    }

    /**
     * @psalm-param scalar|object|null $type
     */
    #[DataProvider('invalidMimeTypeTypes')]
    public function testAddingMimeTypeWithInvalidTypeRaisesException($type): void
    {
        $validator = new MimeType();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid options to validator provided');

        /** @psalm-suppress ArgumentTypeCoercion, PossiblyNullArgument */
        $validator->addMimeType($type);
    }

    public function testAddingMimeTypeUsingMagicFileArrayKeyIgnoresKey(): void
    {
        $validator = new MimeType('image/gif');

        $mimeTypeArray = [
            'magicFile' => 'test.txt',
            'gif'       => 'text',
        ];

        /** @psalm-suppress ArgumentTypeCoercion */
        $validator->addMimeType($mimeTypeArray);

        self::assertSame('image/gif,text', $validator->getMimeType());
        self::assertSame(['image/gif', 'text'], $validator->getMimeType(true));
    }

    public function testIsValidRaisesExceptionWithArrayNotInFilesFormat(): void
    {
        $validator = new MimeType('image\gif');
        $value     = ['foo' => 'bar'];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Value array must be in $_FILES format');

        $validator->isValid($value);
    }
}
