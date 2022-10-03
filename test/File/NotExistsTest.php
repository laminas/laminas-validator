<?php

declare(strict_types=1);

namespace LaminasTest\Validator\File;

use Laminas\Validator\Exception\InvalidArgumentException;
use Laminas\Validator\File\NotExists;
use PHPUnit\Framework\TestCase;

use function basename;
use function current;
use function dirname;
use function is_array;

/**
 * NotExists testbed
 *
 * @group Laminas_Validator
 * @covers \Laminas\Validator\File\NotExists
 */
final class NotExistsTest extends TestCase
{
    /**
     * @psalm-return array<array-key, array{
     *     0: string,
     *     1: string|array{
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
        $testFile   = __DIR__ . '/_files/testsize.mo';
        $baseDir    = dirname($testFile);
        $baseName   = basename($testFile);
        $fileUpload = [
            'tmp_name' => $testFile,
            'name'     => basename($testFile),
            'size'     => 200,
            'error'    => 0,
            'type'     => 'text',
        ];

        return [
            //    Options, isValid Param, Expected value
            [dirname($baseDir), $baseName,   true],
            [$baseDir,          $baseName,   false],
            [$baseDir,          $testFile,   false],
            [dirname($baseDir), $fileUpload, true],
            [$baseDir,          $fileUpload, false],
        ];
    }

    /**
     * Ensures that the validator follows expected behavior
     *
     * @dataProvider basicBehaviorDataProvider
     * @param string|array<string, mixed> $isValidParam
     */
    public function testBasic(string $options, $isValidParam, bool $expected): void
    {
        $validator = new NotExists($options);

        self::assertSame($expected, $validator->isValid($isValidParam));
    }

    /**
     * Ensures that the validator follows expected behavior for legacy Laminas\Transfer API
     *
     * @dataProvider basicBehaviorDataProvider
     * @param string|array<string, mixed> $isValidParam
     */
    public function testLegacy(string $options, $isValidParam, bool $expected): void
    {
        if (! is_array($isValidParam)) {
            self::markTestSkipped('An array is expected for legacy compat tests');
        }

        $validator = new NotExists($options);

        self::assertSame($expected, $validator->isValid($isValidParam['tmp_name'], $isValidParam));
    }

    /** @psalm-return array<array{string|string[], string|string[], bool}> */
    public function getDirectoryProvider(): array
    {
        return [
            ['C:/temp', 'C:/temp', false],
            [['temp', 'dir', 'jpg'], 'temp,dir,jpg', false],
            [['temp', 'dir', 'jpg'], ['temp', 'dir', 'jpg'], true],
        ];
    }

    /**
     * Ensures that getDirectory() returns expected value
     *
     * @dataProvider getDirectoryProvider
     * @param string|string[] $directory
     * @param string|string[] $expected
     */
    public function testGetDirectory($directory, $expected, bool $asArray): void
    {
        $validator = new NotExists($directory);

        self::assertSame($expected, $validator->getDirectory($asArray));
    }

    /** @psalm-return array<array{string|string[], string, string[]}> */
    public function setDirectoryProvider(): array
    {
        return [
            ['gif', 'gif', ['gif']],
            ['jpg, temp', 'jpg,temp', ['jpg', 'temp']],
            [['zip', 'ti'], 'zip,ti', ['zip', 'ti']],
        ];
    }

    /**
     * Ensures that setDirectory() returns expected value
     *
     * @dataProvider setDirectoryProvider
     * @param string|string[] $directory
     * @param string[] $expectedAsArray
     */
    public function testSetDirectory($directory, string $expected, array $expectedAsArray): void
    {
        $validator = new NotExists('temp');
        $validator->setDirectory($directory);

        self::assertSame($expected, $validator->getDirectory());
        self::assertSame($expectedAsArray, $validator->getDirectory(true));
    }

    /**
     * Ensures that addDirectory() returns expected value
     */
    public function testAddDirectory(): void
    {
        $validator = new NotExists('temp');
        $validator->addDirectory('gif');

        self::assertSame('temp,gif', $validator->getDirectory());
        self::assertSame(['temp', 'gif'], $validator->getDirectory(true));

        $validator->addDirectory('jpg, to');

        self::assertSame('temp,gif,jpg,to', $validator->getDirectory());
        self::assertSame(['temp', 'gif', 'jpg', 'to'], $validator->getDirectory(true));

        $validator->addDirectory(['zip', 'ti']);

        self::assertSame('temp,gif,jpg,to,zip,ti', $validator->getDirectory());
        self::assertSame(['temp', 'gif', 'jpg', 'to', 'zip', 'ti'], $validator->getDirectory(true));

        $validator->addDirectory('');

        self::assertSame('temp,gif,jpg,to,zip,ti', $validator->getDirectory());
        self::assertSame(['temp', 'gif', 'jpg', 'to', 'zip', 'ti'], $validator->getDirectory(true));
    }

    /**
     * @group Laminas-11258
     */
    public function testLaminas11258(): void
    {
        $validator = new NotExists();

        self::assertFalse($validator->isValid(__DIR__ . '/_files/testsize.mo'));
        self::assertArrayHasKey('fileNotExistsDoesExist', $validator->getMessages());
        self::assertStringContainsString('File exists', current($validator->getMessages()));
    }

    public function testIsValidShouldThrowInvalidArgumentExceptionForArrayNotInFilesFormat(): void
    {
        $validator = new NotExists();
        $value     = ['foo' => 'bar'];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Value array must be in $_FILES format');

        $validator->isValid($value);
    }

    /**
     * @psalm-return array<string, list<scalar|object|null>>
     */
    public function invalidDirectoryArguments(): array
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
     * @dataProvider invalidDirectoryArguments
     * @psalm-param scalar|object|null $value
     */
    public function testAddingDirectoryUsingInvalidTypeRaisesException($value): void
    {
        $validator = new NotExists();

        $this->expectException(InvalidArgumentException::class);

        $validator->addDirectory($value);
    }
}
