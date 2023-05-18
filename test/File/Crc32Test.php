<?php

declare(strict_types=1);

namespace LaminasTest\Validator\File;

use Laminas\Validator\Exception\InvalidArgumentException;
use Laminas\Validator\File\Crc32;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

use function array_merge;
use function basename;
use function current;
use function is_array;

use const UPLOAD_ERR_NO_FILE;

final class Crc32Test extends TestCase
{
    /**
     * @psalm-return array<array-key, array{
     *     0: string|string[],
     *     1: string|array{
     *         tmp_name: string,
     *         name: string,
     *         size: int,
     *         error: int,
     *         type: string
     *     },
     *     2: bool,
     *     3: string
     * }>
     */
    public static function basicBehaviorDataProvider(): array
    {
        $testFile     = __DIR__ . '/_files/picture.jpg';
        $pictureTests = [
            //    Options, isValid Param, Expected value, Expected message
            ['3f8d07e2',               $testFile, true, ''],
            ['9f8d07e2',               $testFile, false, 'fileCrc32DoesNotMatch'],
            [['9f8d07e2', '3f8d07e2'], $testFile, true, ''],
            [['9f8d07e2', '7f8d07e2'], $testFile, false, 'fileCrc32DoesNotMatch'],
        ];

        $testFile    = __DIR__ . '/_files/nofile.mo';
        $noFileTests = [
            //    Options, isValid Param, Expected value, message
            ['3f8d07e2', $testFile, false, 'fileCrc32NotFound'],
        ];

        $testFile      = __DIR__ . '/_files/testsize.mo';
        $sizeFileTests = [
            //    Options, isValid Param, Expected value, message
            ['ffeb8d5d', $testFile, true,  ''],
            ['9f8d07e2', $testFile, false, 'fileCrc32DoesNotMatch'],
        ];

        // Dupe data in File Upload format
        $testData = array_merge($pictureTests, $noFileTests, $sizeFileTests);
        foreach ($testData as $data) {
            $fileUpload = [
                'tmp_name' => $data[1],
                'name'     => basename($data[1]),
                'size'     => 200,
                'error'    => 0,
                'type'     => 'text',
            ];
            $testData[] = [$data[0], $fileUpload, $data[2], $data[3]];
        }

        return $testData;
    }

    /**
     * Ensures that the validator follows expected behavior
     *
     * @param string|string[] $options
     * @param string|array $isValidParam
     */
    #[DataProvider('basicBehaviorDataProvider')]
    public function testBasic($options, $isValidParam, bool $expected, string $messageKey): void
    {
        $validator = new Crc32($options);

        self::assertSame($expected, $validator->isValid($isValidParam));

        if (! $expected) {
            self::assertArrayHasKey($messageKey, $validator->getMessages());
        }
    }

    /**
     * Ensures that the validator follows expected behavior for legacy Laminas\Transfer API
     *
     * @param string|string[] $options
     * @param string|array $isValidParam
     */
    #[DataProvider('basicBehaviorDataProvider')]
    public function testLegacy($options, $isValidParam, bool $expected, string $messageKey): void
    {
        if (! is_array($isValidParam)) {
            self::markTestSkipped('An array is expected for legacy compat tests');
        }

        $validator = new Crc32($options);

        self::assertSame($expected, $validator->isValid($isValidParam['tmp_name'], $isValidParam));

        if (! $expected) {
            self::assertArrayHasKey($messageKey, $validator->getMessages());
        }
    }

    /**
     * Ensures that getCrc32() returns expected value
     */
    public function testGetCrc32(): void
    {
        $validator = new Crc32('12345');

        self::assertSame(['12345' => 'crc32'], $validator->getCrc32());

        $validator = new Crc32(['12345', '12333', '12344']);

        self::assertSame(['12345' => 'crc32', '12333' => 'crc32', '12344' => 'crc32'], $validator->getCrc32());
    }

    /**
     * Ensures that getHash() returns expected value
     */
    public function testGetHash(): void
    {
        $validator = new Crc32('12345');

        self::assertSame(['12345' => 'crc32'], $validator->getHash());

        $validator = new Crc32(['12345', '12333', '12344']);

        self::assertSame(['12345' => 'crc32', '12333' => 'crc32', '12344' => 'crc32'], $validator->getHash());
    }

    /**
     * Ensures that setCrc32() returns expected value
     */
    public function testSetCrc32(): void
    {
        $validator = new Crc32('12345');
        $validator->setCrc32('12333');

        self::assertSame(['12333' => 'crc32'], $validator->getCrc32());

        $validator->setCrc32(['12321', '12121']);

        self::assertSame(['12321' => 'crc32', '12121' => 'crc32'], $validator->getCrc32());
    }

    /**
     * Ensures that setHash() returns expected value
     */
    public function testSetHash(): void
    {
        $validator = new Crc32('12345');
        $validator->setHash('12333');

        self::assertSame(['12333' => 'crc32'], $validator->getCrc32());

        $validator->setHash(['12321', '12121']);

        self::assertSame(['12321' => 'crc32', '12121' => 'crc32'], $validator->getCrc32());
    }

    /**
     * Ensures that addCrc32() returns expected value
     */
    public function testAddCrc32(): void
    {
        $validator = new Crc32('12345');
        $validator->addCrc32('12344');

        self::assertSame(['12345' => 'crc32', '12344' => 'crc32'], $validator->getCrc32());

        $validator->addCrc32(['12321', '12121']);

        self::assertSame(
            ['12345' => 'crc32', '12344' => 'crc32', '12321' => 'crc32', '12121' => 'crc32'],
            $validator->getCrc32()
        );
    }

    /**
     * Ensures that addHash() returns expected value
     */
    public function testAddHash(): void
    {
        $validator = new Crc32('12345');
        $validator->addHash('12344');

        self::assertSame(['12345' => 'crc32', '12344' => 'crc32'], $validator->getCrc32());

        $validator->addHash(['12321', '12121']);

        self::assertSame(
            ['12345' => 'crc32', '12344' => 'crc32', '12321' => 'crc32', '12121' => 'crc32'],
            $validator->getCrc32()
        );
    }

    #[Group('Laminas-11258')]
    public function testLaminas11258(): void
    {
        $validator = new Crc32('3f8d07e2');

        self::assertFalse($validator->isValid(__DIR__ . '/_files/nofile.mo'));
        self::assertArrayHasKey('fileCrc32NotFound', $validator->getMessages());
        self::assertStringContainsString('does not exist', current($validator->getMessages()));
    }

    public function testEmptyFileShouldReturnFalseAndDisplayNotFoundMessage(): void
    {
        $validator = new Crc32();

        self::assertFalse($validator->isValid(''));
        self::assertArrayHasKey(Crc32::NOT_FOUND, $validator->getMessages());

        $filesArray = [
            'name'     => '',
            'size'     => 0,
            'tmp_name' => '',
            'error'    => UPLOAD_ERR_NO_FILE,
            'type'     => '',
        ];

        self::assertFalse($validator->isValid($filesArray));
        self::assertArrayHasKey(Crc32::NOT_FOUND, $validator->getMessages());
    }

    public function testShouldThrowInvalidArgumentExceptionForArrayValueNotInFilesFormat(): void
    {
        $validator    = new Crc32();
        $invalidArray = ['foo' => 'bar'];

        $this->expectException(InvalidArgumentException::class);

        $validator->isValid($invalidArray);
    }
}
