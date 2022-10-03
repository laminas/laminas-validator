<?php

declare(strict_types=1);

namespace LaminasTest\Validator\File;

use Laminas\Validator\Exception\InvalidArgumentException;
use Laminas\Validator\File\Hash;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

use function array_merge;
use function basename;
use function current;
use function is_array;
use function sprintf;

use const UPLOAD_ERR_NO_FILE;

/**
 * Hash testbed
 *
 * @group Laminas_Validator
 * @covers \Laminas\Validator\File\Hash
 */
final class HashTest extends TestCase
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
    public function basicBehaviorDataProvider(): array
    {
        $testFile     = __DIR__ . '/_files/picture.jpg';
        $pictureTests = [
            //    Options, isValid Param, Expected value, Expected message
            ['3f8d07e2',               $testFile, true, ''],
            ['9f8d07e2',               $testFile, false, 'fileHashDoesNotMatch'],
            [['9f8d07e2', '3f8d07e2'], $testFile, true, ''],
            [['9f8d07e2', '7f8d07e2'], $testFile, false, 'fileHashDoesNotMatch'],
            [
                ['ed74c22109fe9f110579f77b053b8bc3', 'algorithm' => 'md5'],
                $testFile,
                true,
                '',
            ],
            [
                ['4d74c22109fe9f110579f77b053b8bc3', 'algorithm' => 'md5'],
                $testFile,
                false,
                'fileHashDoesNotMatch',
            ],
            [
                ['4d74c22109fe9f110579f77b053b8bc3', 'ed74c22109fe9f110579f77b053b8bc3', 'algorithm' => 'md5'],
                $testFile,
                true,
                '',
            ],
            [
                ['4d74c22109fe9f110579f77b053b8bc3', '7d74c22109fe9f110579f77b053b8bc3', 'algorithm' => 'md5'],
                $testFile,
                false,
                'fileHashDoesNotMatch',
            ],
        ];

        $testFile    = __DIR__ . '/_files/nofile.mo';
        $noFileTests = [
            //    Options, isValid Param, Expected value, message
            ['3f8d07e2', $testFile, false, 'fileHashNotFound'],
        ];

        $testFile      = __DIR__ . '/_files/testsize.mo';
        $sizeFileTests = [
            //    Options, isValid Param, Expected value, message
            ['ffeb8d5d', $testFile, true,  ''],
            ['9f8d07e2', $testFile, false, 'fileHashDoesNotMatch'],
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
     * @dataProvider basicBehaviorDataProvider
     * @param string|array $options
     * @param string|array $isValidParam
     */
    public function testBasic($options, $isValidParam, bool $expected, string $messageKey): void
    {
        $validator = new Hash($options);

        self::assertSame($expected, $validator->isValid($isValidParam));

        if (! $expected) {
            self::assertArrayHasKey($messageKey, $validator->getMessages());
        }
    }

    /**
     * Ensures that the validator follows expected behavior for legacy Laminas\Transfer API
     *
     * @dataProvider basicBehaviorDataProvider
     * @param string|array $options
     * @param string|array $isValidParam
     */
    public function testLegacy($options, $isValidParam, bool $expected, string $messageKey): void
    {
        if (! is_array($isValidParam)) {
            self::markTestSkipped('An array is expected for legacy compat tests');
        }

        $validator = new Hash($options);

        self::assertSame($expected, $validator->isValid($isValidParam['tmp_name'], $isValidParam));

        if (! $expected) {
            self::assertArrayHasKey($messageKey, $validator->getMessages());
        }
    }

    /** @psalm-return array<array{string|string[], array<numeric, string>}> */
    public function hashProvider(): array
    {
        return [
            ['12345', ['12345' => 'crc32']],
            [['12345', '12333', '12344'], ['12345' => 'crc32', '12333' => 'crc32', '12344' => 'crc32']],
        ];
    }

    /**
     * Ensures that getHash() returns expected value
     *
     * @dataProvider hashProvider
     * @param string|string[] $hash
     * @psalm-param array<numeric, string> $expected
     */
    public function testGetHash($hash, array $expected): void
    {
        $validator = new Hash($hash);

        self::assertSame($expected, $validator->getHash());
    }

    /**
     * Ensures that setHash() returns expected value
     *
     * @dataProvider hashProvider
     * @param string|string[] $hash
     * @psalm-param array<numeric, string> $expected
     */
    public function testSetHash($hash, array $expected): void
    {
        $validator = new Hash('12333');
        $validator->setHash($hash);

        self::assertSame($expected, $validator->getHash());
    }

    /**
     * Ensures that addHash() returns expected value
     */
    public function testAddHash(): void
    {
        $validator = new Hash('12345');
        $validator->addHash('12344');

        self::assertSame(['12345' => 'crc32', '12344' => 'crc32'], $validator->getHash());

        $validator->addHash(['12321', '12121']);

        self::assertSame(
            ['12345' => 'crc32', '12344' => 'crc32', '12321' => 'crc32', '12121' => 'crc32'],
            $validator->getHash()
        );
    }

    /**
     * @group Laminas-11258
     */
    public function testLaminas11258(): void
    {
        $validator = new Hash('3f8d07e2');

        self::assertFalse($validator->isValid(__DIR__ . '/_files/nofile.mo'));
        self::assertArrayHasKey('fileHashNotFound', $validator->getMessages());
        self::assertStringContainsString('does not exist', current($validator->getMessages()));
    }

    public function testEmptyFileShouldReturnFalseAndDisplayNotFoundMessage(): void
    {
        $validator = new Hash();

        self::assertFalse($validator->isValid(''));
        self::assertArrayHasKey(Hash::NOT_FOUND, $validator->getMessages());

        $filesArray = [
            'name'     => '',
            'size'     => 0,
            'tmp_name' => '',
            'error'    => UPLOAD_ERR_NO_FILE,
            'type'     => '',
        ];

        self::assertFalse($validator->isValid($filesArray));
        self::assertArrayHasKey(Hash::NOT_FOUND, $validator->getMessages());
    }

    /**
     * @psalm-return array<string, array{0: mixed}>
     */
    public function invalidHashTypes(): array
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
     * @dataProvider invalidHashTypes
     * @param mixed $value
     */
    public function testAddHashRaisesExceptionForInvalidType($value): void
    {
        $validator = new Hash('12345');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('False parameter given');

        $validator->addHash($value);
    }

    public function testAddHashRaisesExceptionWithInvalidAlgorithm(): void
    {
        $validator = new Hash('12345');
        $algorithm = 'foobar123';
        $options   = ['algorithm' => $algorithm];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf("Unknown algorithm '%s'", $algorithm));

        $validator->addHash($options);
    }

    public function testIsValidRaisesExceptionForArrayValueNotInFilesFormat(): void
    {
        $validator = new Hash('12345');
        $value     = ['foo' => 'bar'];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Value array must be in $_FILES format');

        $validator->isValid($value);
    }

    public function testConstructorCanAcceptAllOptionsAsDiscreteArguments(): void
    {
        $algorithm = 'md5';
        $validator = new Hash('12345', $algorithm);

        $r = new ReflectionProperty($validator, 'options');
        $r->setAccessible(true);
        $options = $r->getValue($validator);

        self::assertSame($algorithm, $options['algorithm']);
    }

    /**
     * @dataProvider invalidHashTypes
     * @param mixed $hash
     */
    public function testInvalidHashProvidedInArrayFormat($hash): void
    {
        $validator = new Hash('12345');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Hash must be a string');

        $validator->addHash([$hash]);
    }

    public function testIntHash(): void
    {
        $validator = new Hash('10713230');

        self::assertTrue($validator->isValid(__DIR__ . '/_files/crc32-int.pdf'));
    }

    public function testHashMustMatchWithTheAlgorithm(): void
    {
        $validator = new Hash();
        // swapped hashes for given algorithms
        $validator->addHash(['6507f172bceb9ed0cc59246d41569c4d', 'algorithm' => 'crc32']);
        $validator->addHash(['10713230', 'algorithm' => 'md5']);

        self::assertFalse($validator->isValid(__DIR__ . '/_files/crc32-int.pdf'));
    }
}
