<?php

declare(strict_types=1);

namespace LaminasTest\Validator\File;

use Laminas\Validator\Exception\InvalidArgumentException;
use Laminas\Validator\File\Size;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

use function basename;
use function current;
use function is_array;

use const UPLOAD_ERR_NO_FILE;

final class SizeTest extends TestCase
{
    /**
     * @psalm-return array<array-key, array{
     *     0: int|array<string, int|string>,
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
    public static function basicBehaviorDataProvider(): array
    {
        $testFile = __DIR__ . '/_files/testsize.mo';
        $testData = [
            //    Options, isValid Param, Expected value
            [794,     $testFile,     true],
            [500,     $testFile,     false],
            [['min' => 0, 'max' => 10000], $testFile, true],
            [['min' => 0, 'max' => '10 MB'], $testFile, true],
            [['min' => '4B', 'max' => '10 MB'], $testFile, true],
            [['min' => 0, 'max' => '10MB'], $testFile, true],
            [['min' => 0, 'max' => '10  MB'], $testFile, true],
            [['min' => 794], $testFile, true],
            [['min' => 0, 'max' => 500], $testFile, false],
        ];

        // Dupe data in File Upload format
        foreach ($testData as $data) {
            $fileUpload = [
                'tmp_name' => $data[1],
                'name'     => basename($data[1]),
                'size'     => 200,
                'error'    => 0,
                'type'     => 'text',
            ];
            $testData[] = [$data[0], $fileUpload, $data[2]];
        }

        return $testData;
    }

    /**
     * Ensures that the validator follows expected behavior
     *
     * @param int|array $options
     * @param string|array $isValidParam
     */
    #[DataProvider('basicBehaviorDataProvider')]
    public function testBasic($options, $isValidParam, bool $expected): void
    {
        $validator = new Size($options);

        self::assertSame($expected, $validator->isValid($isValidParam));
    }

    /**
     * Ensures that the validator follows expected behavior for legacy Laminas\Transfer API
     *
     * @param int|array $options
     * @param string|array $isValidParam
     */
    #[DataProvider('basicBehaviorDataProvider')]
    public function testLegacy($options, $isValidParam, bool $expected): void
    {
        if (! is_array($isValidParam)) {
            self::markTestSkipped('An array is expected for legacy compat tests');
        }

        $validator = new Size($options);

        self::assertSame($expected, $validator->isValid($isValidParam['tmp_name'], $isValidParam));
    }

    /**
     * Ensures that getMin() returns expected value
     */
    public function testGetMin(): void
    {
        $validator = new Size(['min' => 1, 'max' => 100]);

        self::assertSame('1B', $validator->getMin());

        $validator = new Size(['min' => 1, 'max' => 100, 'useByteString' => false]);

        self::assertSame(1, $validator->getMin());

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('greater than or equal');

        new Size(['min' => 100, 'max' => 1]);
    }

    /**
     * Ensures that setMin() returns expected value
     */
    public function testSetMin(): void
    {
        $validator = new Size(['min' => 1000, 'max' => 10000]);
        $validator->setMin(100);

        self::assertSame('100B', $validator->getMin());

        $validator = new Size(['min' => 1000, 'max' => 10000, 'useByteString' => false]);
        $validator->setMin(100);

        self::assertSame(100, $validator->getMin());

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('less than or equal');

        $validator->setMin(20000);
    }

    /**
     * Ensures that getMax() returns expected value
     */
    public function testGetMax(): void
    {
        $validator = new Size(['min' => 1, 'max' => 100, 'useByteString' => false]);

        self::assertSame(100, $validator->getMax());

        $validator = new Size(['min' => 1, 'max' => 100000]);

        self::assertSame('97.66kB', $validator->getMax());

        $validator = new Size(2000);

        self::assertSame('1.95kB', $validator->getMax());

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('greater than or equal');

        new Size(['min' => 100, 'max' => 1]);
    }

    /** @psalm-return array<array{string|int, string}> */
    public static function setMaxProvider(): array
    {
        return [
            [1_000_000, '976.56kB'],
            ['100 AB', '100B'],
            ['100 kB', '100kB'],
            ['100 MB', '100MB'],
            ['1 GB', '1GB'],
            ['0.001 TB', '1.02GB'],
            ['0.000001 PB', '1.05GB'],
            ['0.000000001 EB', '1.07GB'],
            ['0.000000000001 ZB', '1.1GB'],
            ['0.000000000000001 YB', '1.13GB'],
        ];
    }

    /**
     * Ensures that setMax() returns expected value
     *
     * @param string|int $max
     */
    #[DataProvider('setMaxProvider')]
    public function testSetMax($max, string $expected): void
    {
        $validator = new Size(['max' => 0, 'useByteString' => true]);
        self::assertSame('0B', $validator->getMax());

        $validator->setMax($max);
        self::assertSame($expected, $validator->getMax());
    }

    /**
     * Ensures that the validator returns size infos
     */
    public function testFailureMessage(): void
    {
        $validator = new Size(['min' => 9999, 'max' => 10000]);

        self::assertFalse($validator->isValid(__DIR__ . '/_files/testsize.mo'));

        $messages = $validator->getMessages();

        self::assertStringContainsString('9.76kB', current($messages));
        self::assertStringContainsString('794B', current($messages));

        $validator = new Size(['min' => 9999, 'max' => 10000, 'useByteString' => false]);

        self::assertFalse($validator->isValid(__DIR__ . '/_files/testsize.mo'));

        $messages = $validator->getMessages();

        self::assertStringContainsString('9999', current($messages));
        self::assertStringContainsString('794', current($messages));
    }

    #[Group('Laminas-11258')]
    public function testLaminas11258(): void
    {
        $validator = new Size(['min' => 1, 'max' => 10000]);

        self::assertFalse($validator->isValid(__DIR__ . '/_files/nofile.mo'));
        self::assertArrayHasKey('fileSizeNotFound', $validator->getMessages());
        self::assertStringContainsString('does not exist', current($validator->getMessages()));
    }

    public function testEmptyFileShouldReturnFalseAndDisplayNotFoundMessage(): void
    {
        $validator = new Size();

        self::assertFalse($validator->isValid(''));
        self::assertArrayHasKey(Size::NOT_FOUND, $validator->getMessages());

        $filesArray = [
            'name'     => '',
            'size'     => 0,
            'tmp_name' => '',
            'error'    => UPLOAD_ERR_NO_FILE,
            'type'     => '',
        ];

        self::assertFalse($validator->isValid($filesArray));
        self::assertArrayHasKey(Size::NOT_FOUND, $validator->getMessages());
    }

    /**
     * @psalm-return array<string, array{0: mixed}>
     */
    public static function invalidMinMaxValues(): array
    {
        return [
            'null'   => [null],
            'true'   => [true],
            'false'  => [false],
            'array'  => [[100]],
            'object' => [(object) []],
        ];
    }

    #[DataProvider('invalidMinMaxValues')]
    public function testSetMinWithInvalidArgument(mixed $value): void
    {
        $validator = new Size(['min' => 0, 'max' => 2000]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid options to validator provided');

        $validator->setMin($value);
    }

    #[DataProvider('invalidMinMaxValues')]
    public function testSetMaxWithInvalidArgument(mixed $value): void
    {
        $validator = new Size(['min' => 0, 'max' => 2000]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid options to validator provided');

        $validator->setMax($value);
    }
}
