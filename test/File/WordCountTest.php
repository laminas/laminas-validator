<?php

declare(strict_types=1);

namespace LaminasTest\Validator\File;

use Laminas\Validator\Exception\InvalidArgumentException;
use Laminas\Validator\File\WordCount;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

use function basename;
use function current;
use function is_array;

use const UPLOAD_ERR_NO_FILE;

final class WordCountTest extends TestCase
{
    /**
     * @psalm-return array<array-key, array{
     *     0: int|array<string, int>,
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
        $testFile = __DIR__ . '/_files/wordcount.txt';
        $testData = [
            //    Options, isValid Param, Expected value
            [15,      $testFile,     true],
            [4,       $testFile,     false],
            [['min' => 0, 'max' => 10], $testFile, true],
            [['min' => 10, 'max' => 15], $testFile, false],
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
        $validator = new WordCount($options);

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

        $validator = new WordCount($options);

        self::assertSame($expected, $validator->isValid($isValidParam['tmp_name'], $isValidParam));
    }

    /**
     * Ensures that getMin() returns expected value
     */
    public function testGetMin(): void
    {
        $validator = new WordCount(['min' => 1, 'max' => 5]);
        self::assertSame(1, $validator->getMin());

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('greater than or equal');

        new WordCount(['min' => 5, 'max' => 1]);
    }

    /**
     * Ensures that setMin() returns expected value
     */
    public function testSetMin(): void
    {
        $validator = new WordCount(['min' => 1000, 'max' => 10000]);
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
        $validator = new WordCount(['min' => 1, 'max' => 100]);

        self::assertSame(100, $validator->getMax());

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('greater than or equal');

        new WordCount(['min' => 5, 'max' => 1]);
    }

    /**
     * Ensures that setMax() returns expected value
     */
    public function testSetMax(): void
    {
        $validator = new WordCount(['min' => 1000, 'max' => 10000]);
        $validator->setMax(1_000_000);

        self::assertSame(1_000_000, $validator->getMax());

        $validator->setMin(100);

        self::assertSame(1_000_000, $validator->getMax());
    }

    #[Group('Laminas-11258')]
    public function testLaminas11258(): void
    {
        $validator = new WordCount(['min' => 1, 'max' => 10000]);

        self::assertFalse($validator->isValid(__DIR__ . '/_files/nofile.mo'));
        self::assertArrayHasKey('fileWordCountNotFound', $validator->getMessages());
        self::assertStringContainsString('does not exist', current($validator->getMessages()));
    }

    public function testEmptyFileShouldReturnFalseAndDisplayNotFoundMessage(): void
    {
        $validator = new WordCount();

        self::assertFalse($validator->isValid(''));
        self::assertArrayHasKey(WordCount::NOT_FOUND, $validator->getMessages());

        $filesArray = [
            'name'     => '',
            'size'     => 0,
            'tmp_name' => '',
            'error'    => UPLOAD_ERR_NO_FILE,
            'type'     => '',
        ];

        self::assertFalse($validator->isValid($filesArray));
        self::assertArrayHasKey(WordCount::NOT_FOUND, $validator->getMessages());
    }

    public function testCanSetMinValueUsingOptionsArray(): void
    {
        $validator = new WordCount(['min' => 1000, 'max' => 10000]);
        $minValue  = 33;
        $options   = ['min' => $minValue];

        $validator->setMin($options);

        self::assertSame($minValue, $validator->getMin());
    }

    /**
     * @psalm-return array<string, array{0: mixed}>
     */
    public static function invalidMinMaxValues(): array
    {
        return [
            'null'               => [null],
            'true'               => [true],
            'false'              => [false],
            'non-numeric-string' => ['not-a-good-value'],
            'array-without-keys' => [[100]],
            'object'             => [(object) []],
        ];
    }

    #[DataProvider('invalidMinMaxValues')]
    public function testSettingMinValueRaisesExceptionForInvalidType(mixed $value): void
    {
        $validator = new WordCount(['min' => 1000, 'max' => 10000]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid options to validator provided');

        $validator->setMin($value);
    }

    public function testCanSetMaxValueUsingOptionsArray(): void
    {
        $validator = new WordCount(['min' => 1000, 'max' => 10000]);
        $maxValue  = 33_333_333;
        $options   = ['max' => $maxValue];

        $validator->setMax($options);

        self::assertSame($maxValue, $validator->getMax());
    }

    #[DataProvider('invalidMinMaxValues')]
    public function testSettingMaxValueRaisesExceptionForInvalidType(mixed $value): void
    {
        $validator = new WordCount(['min' => 1000, 'max' => 10000]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid options to validator provided');

        $validator->setMax($value);
    }

    public function testIsValidShouldThrowInvalidArgumentExceptionForArrayNotInFilesFormat(): void
    {
        $validator = new WordCount(['min' => 1, 'max' => 10000]);
        $value     = ['foo' => 'bar'];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Value array must be in $_FILES format');

        $validator->isValid($value);
    }

    public function testConstructCanAcceptAllOptionsAsDiscreteArguments(): void
    {
        $min       = 1;
        $max       = 10000;
        $validator = new WordCount($min, $max);

        self::assertSame($min, $validator->getMin());
        self::assertSame($max, $validator->getMax());
    }
}
