<?php

declare(strict_types=1);

namespace LaminasTest\Validator\File;

use Laminas\Validator\Exception\InvalidArgumentException;
use Laminas\Validator\File\Count;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\UploadedFileInterface;
use ReflectionClass;

use function basename;

/**
 * @group Laminas_Validator
 * @covers \Laminas\Validator\File\Count
 */
final class CountTest extends TestCase
{
    /**
     * Ensures that the validator follows expected behavior
     *
     * @dataProvider basicDataProvider
     * @param array|int $options
     */
    public function testBasic($options, bool $expected1, bool $expected2, bool $expected3, bool $expected4): void
    {
        $validator = new Count($options);

        self::assertSame(
            $expected1,
            $validator->isValid(__DIR__ . '/_files/testsize.mo')
        );
        self::assertSame(
            $expected2,
            $validator->isValid(__DIR__ . '/_files/testsize2.mo')
        );
        self::assertSame(
            $expected3,
            $validator->isValid(__DIR__ . '/_files/testsize3.mo')
        );
        self::assertSame(
            $expected4,
            $validator->isValid(__DIR__ . '/_files/testsize4.mo')
        );
    }

    /**
     * @psalm-return array<string, array{
     *     0: int|array<string, int>,
     *     1: bool,
     *     2: bool,
     *     3: bool,
     *     4: bool
     * }>
     */
    public function basicDataProvider(): array
    {
        return [
            // phpcs:disable
            'no minimum; maximum: 5; integer' => [5,                        true,  true, true, true],
            'no minimum; maximum: 5; array'   => [['max' => 5],             true,  true, true, true],
            'minimum: 0; maximum: 3'        => [['min' => 0, 'max' => 3], true,  true, true, false],
            'minimum: 2; maximum: 3'        => [['min' => 2, 'max' => 3], false, true, true, false],
            'minimum: 2; no maximum'          => [['min' => 2],             false, true, true, true],
            // phpcs:enable
        ];
    }

    /**
     * Ensures that getMin() returns expected value
     */
    public function testGetMin(): void
    {
        $validator = new Count(['min' => 1, 'max' => 5]);

        self::assertSame(1, $validator->getMin());
    }

    public function testGetMinGreaterThanOrEqualThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('greater than or equal');

        new Count(['min' => 5, 'max' => 1]);
    }

    /**
     * Ensures that setMin() returns expected value
     */
    public function testSetMin(): void
    {
        $validator = new Count(['min' => 1000, 'max' => 10000]);
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
        $validator = new Count(['min' => 1, 'max' => 100]);

        self::assertSame(100, $validator->getMax());

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('greater than or equal');

        new Count(['min' => 5, 'max' => 1]);
    }

    /**
     * Ensures that setMax() returns expected value
     */
    public function testSetMax(): void
    {
        $validator = new Count(['min' => 1000, 'max' => 10000]);
        $validator->setMax(1_000_000);

        self::assertSame(1_000_000, $validator->getMax());

        $validator->setMin(100);

        self::assertSame(1_000_000, $validator->getMax());
    }

    public function testCanSetMaxValueUsingAnArrayWithMaxKey(): void
    {
        $validator   = new Count(['min' => 1000, 'max' => 10000]);
        $maxValue    = 33_333_333;
        $setMaxArray = ['max' => $maxValue];

        $validator->setMax($setMaxArray);

        self::assertSame($maxValue, $validator->getMax());
    }

    /**
     * @psalm-return array<string, array{0: mixed}>
     */
    public function invalidMinMaxValues(): array
    {
        return [
            'null'           => [null],
            'true'           => [true],
            'false'          => [false],
            'invalid-string' => ['will-not-work'],
            'invalid-array'  => [[100]],
            'object'         => [(object) []],
        ];
    }

    /**
     * @dataProvider invalidMinMaxValues
     * @param mixed $max
     */
    public function testSettingMaxWithInvalidArgumentRaisesException($max): void
    {
        $validator = new Count(['min' => 1000, 'max' => 10000]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid options to validator provided');

        $validator->setMax($max);
    }

    public function testCanSetMinUsingAnArrayWithAMinKey(): void
    {
        $validator   = new Count(['min' => 1000, 'max' => 10000]);
        $minValue    = 33;
        $setMinArray = ['min' => $minValue];

        $validator->setMin($setMinArray);

        self::assertSame($minValue, $validator->getMin());
    }

    /**
     * @dataProvider invalidMinMaxValues
     * @param mixed $min
     */
    public function testSettingMinWithInvalidArgumentRaisesException($min): void
    {
        $validator = new Count(['min' => 1000, 'max' => 10000]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid options to validator provided');

        $validator->setMin($min);
    }

    public function testThrowErrorReturnsFalseAndSetsMessageWhenProvidedWithArrayRepresentingTooFewFiles(): void
    {
        $validator = new Count(['min' => 1000, 'max' => 10000]);
        $filename  = 'test.txt';
        $fileArray = ['name' => $filename];

        $reflection = new ReflectionClass($validator);

        $method = $reflection->getMethod('throwError');
        $method->setAccessible(true);

        $property = $reflection->getProperty('value');
        $property->setAccessible(true);

        $result = $method->invoke($validator, $fileArray, Count::TOO_FEW);

        self::assertFalse($result);
        self::assertSame($filename, $property->getValue($validator));
    }

    public function testThrowErrorReturnsFalseAndSetsMessageWhenProvidedWithASingleFilename(): void
    {
        $validator  = new Count(['min' => 1000, 'max' => 10000]);
        $filename   = 'test.txt';
        $reflection = new ReflectionClass($validator);

        $method = $reflection->getMethod('throwError');
        $method->setAccessible(true);

        $property = $reflection->getProperty('value');
        $property->setAccessible(true);

        $result = $method->invoke($validator, $filename, Count::TOO_FEW);

        self::assertFalse($result);
        self::assertSame($filename, $property->getValue($validator));
    }

    public function testCanProvideMinAndMaxAsDiscreteConstructorArguments(): void
    {
        $min       = 1000;
        $max       = 10000;
        $validator = new Count($min, $max);

        self::assertSame($min, $validator->getMin());
        self::assertSame($max, $validator->getMax());
    }

    public function testPsr7FileTypes(): void
    {
        $testFile = __DIR__ . '/_files/testsize.mo';

        $upload = $this->createMock(UploadedFileInterface::class);
        $upload->method('getClientFilename')->willReturn(basename($testFile));

        $validValidator = new Count(['min' => 1]);

        $this->assertTrue($validValidator->isValid($upload));
        $this->assertTrue($validValidator->isValid($upload, []));

        $invalidMinValidator = new Count(['min' => 2]);
        $invalidMaxValidator = new Count(['max' => 0]);

        $this->assertFalse($invalidMinValidator->isValid($upload));
        $this->assertFalse($invalidMaxValidator->isValid($upload));
    }
}
