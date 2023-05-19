<?php

declare(strict_types=1);

namespace LaminasTest\Validator\File;

use Laminas\Validator\Exception\InvalidArgumentException;
use Laminas\Validator\File\Sha1;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

use function array_merge;
use function basename;
use function current;
use function is_array;

use const UPLOAD_ERR_NO_FILE;

final class Sha1Test extends TestCase
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
            ['b2a5334847b4328e7d19d9b41fd874dffa911c98', $testFile, true,  ''],
            ['52a5334847b4328e7d19d9b41fd874dffa911c98', $testFile, false, 'fileSha1DoesNotMatch'],
            [
                ['42a5334847b4328e7d19d9b41fd874dffa911c98', 'b2a5334847b4328e7d19d9b41fd874dffa911c98'],
                $testFile,
                true,
                '',
            ],
            [
                ['42a5334847b4328e7d19d9b41fd874dffa911c98', '72a5334847b4328e7d19d9b41fd874dffa911c98'],
                $testFile,
                false,
                'fileSha1DoesNotMatch',
            ],
        ];

        $testFile    = __DIR__ . '/_files/nofile.mo';
        $noFileTests = [
            //    Options, isValid Param, Expected value, message
            ['b2a5334847b4328e7d19d9b41fd874dffa911c98', $testFile, false, 'fileSha1NotFound'],
        ];

        // Dupe data in File Upload format
        $testData = array_merge($pictureTests, $noFileTests);
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
        $validator = new Sha1($options);

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

        $validator = new Sha1($options);

        self::assertSame($expected, $validator->isValid($isValidParam['tmp_name'], $isValidParam));

        if (! $expected) {
            self::assertArrayHasKey($messageKey, $validator->getMessages());
        }
    }

    /** @psalm-return array<array{string|list<string>, array<numeric, string>}> */
    public static function getHashProvider(): array
    {
        return [
            ['12333', ['12333' => 'sha1']],
            [['12345', '12333', '12344'], ['12345' => 'sha1', '12333' => 'sha1', '12344' => 'sha1']],
        ];
    }

    /**
     * Ensures that getSha1() returns expected value
     *
     * @psalm-param string|list<string> $hash
     * @psalm-param array<numeric, string> $expected
     */
    #[DataProvider('getHashProvider')]
    public function testGetSha1($hash, array $expected): void
    {
        $validator = new Sha1($hash);

        self::assertSame($expected, $validator->getSha1());
    }

    /**
     * Ensures that getHash() returns expected value
     *
     * @psalm-param string|list<string> $hash
     * @psalm-param array<numeric, string> $expected
     */
    #[DataProvider('getHashProvider')]
    public function testGetHash($hash, array $expected): void
    {
        $validator = new Sha1($hash);

        self::assertSame($expected, $validator->getHash());
    }

    /**
     * Ensures that setSha1() returns expected value
     *
     * @psalm-param string|list<string> $hash
     * @psalm-param array<numeric, string> $expected
     */
    #[DataProvider('getHashProvider')]
    public function testSetSha1($hash, array $expected): void
    {
        $validator = new Sha1('12345');
        $validator->setSha1($hash);

        self::assertSame($expected, $validator->getSha1());
    }

    /**
     * Ensures that setHash() returns expected value
     *
     * @psalm-param string|list<string> $hash
     * @psalm-param array<numeric, string> $expected
     */
    #[DataProvider('getHashProvider')]
    public function testSetHash($hash, array $expected): void
    {
        $validator = new Sha1('12345');
        $validator->setHash($hash);

        self::assertSame($expected, $validator->getSha1());
    }

    /**
     * Ensures that addSha1() returns expected value
     */
    public function testAddSha1(): void
    {
        $validator = new Sha1('12345');
        $validator->addSha1('12344');

        self::assertSame(['12345' => 'sha1', '12344' => 'sha1'], $validator->getSha1());

        $validator->addSha1(['12321', '12121']);

        self::assertSame(
            ['12345' => 'sha1', '12344' => 'sha1', '12321' => 'sha1', '12121' => 'sha1'],
            $validator->getSha1()
        );
    }

    /**
     * Ensures that addHash() returns expected value
     */
    public function testAddHash(): void
    {
        $validator = new Sha1('12345');
        $validator->addHash('12344');

        self::assertSame(['12345' => 'sha1', '12344' => 'sha1'], $validator->getSha1());

        $validator->addHash(['12321', '12121']);

        self::assertSame(
            ['12345' => 'sha1', '12344' => 'sha1', '12321' => 'sha1', '12121' => 'sha1'],
            $validator->getSha1()
        );
    }

    #[Group('Laminas-11258')]
    public function testLaminas11258(): void
    {
        $validator = new Sha1('12345');

        self::assertFalse($validator->isValid(__DIR__ . '/_files/nofile.mo'));
        self::assertArrayHasKey('fileSha1NotFound', $validator->getMessages());
        self::assertStringContainsString('does not exist', current($validator->getMessages()));
    }

    public function testEmptyFileShouldReturnFalseAndDisplayNotFoundMessage(): void
    {
        $validator = new Sha1();

        self::assertFalse($validator->isValid(''));
        self::assertArrayHasKey(Sha1::NOT_FOUND, $validator->getMessages());

        $filesArray = [
            'name'     => '',
            'size'     => 0,
            'tmp_name' => '',
            'error'    => UPLOAD_ERR_NO_FILE,
            'type'     => '',
        ];

        self::assertFalse($validator->isValid($filesArray));
        self::assertArrayHasKey(Sha1::NOT_FOUND, $validator->getMessages());
    }

    public function testIsValidShouldThrowInvalidArgumentExceptionForArrayNotInFilesFormat(): void
    {
        $validator = new Sha1();
        $value     = ['foo' => 'bar'];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Value array must be in $_FILES format');

        $validator->isValid($value);
    }
}
