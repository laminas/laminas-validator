<?php

/**
 * @see       https://github.com/laminas/laminas-validator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-validator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-validator/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Validator\File;

use Laminas\Validator\Exception\InvalidArgumentException;
use Laminas\Validator\File;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * @group      Laminas_Validator
 */
class CountTest extends TestCase
{
    /**
     * Ensures that the validator follows expected behavior
     *
     * @dataProvider basicDataProvider
     * @param array|int $options
     * @return void
     */
    public function testBasic($options, bool $expected1, bool $expected2, bool $expected3, bool $expected4)
    {
        $validator = new File\Count($options);
        $this->assertSame(
            $expected1,
            $validator->isValid(__DIR__ . '/_files/testsize.mo')
        );
        $this->assertSame(
            $expected2,
            $validator->isValid(__DIR__ . '/_files/testsize2.mo')
        );
        $this->assertSame(
            $expected3,
            $validator->isValid(__DIR__ . '/_files/testsize3.mo')
        );
        $this->assertSame(
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
     *
     * @return void
     */
    public function testGetMin()
    {
        $validator = new File\Count(['min' => 1, 'max' => 5]);
        $this->assertEquals(1, $validator->getMin());
    }

    public function testGetMinGreaterThanOrEqualThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('greater than or equal');
        $validator = new File\Count(['min' => 5, 'max' => 1]);
    }

    /**
     * Ensures that setMin() returns expected value
     *
     * @return void
     */
    public function testSetMin()
    {
        $validator = new File\Count(['min' => 1000, 'max' => 10000]);
        $validator->setMin(100);
        $this->assertEquals(100, $validator->getMin());

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('less than or equal');
        $validator->setMin(20000);
    }

    /**
     * Ensures that getMax() returns expected value
     *
     * @return void
     */
    public function testGetMax()
    {
        $validator = new File\Count(['min' => 1, 'max' => 100]);
        $this->assertEquals(100, $validator->getMax());

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('greater than or equal');
        $validator = new File\Count(['min' => 5, 'max' => 1]);
    }

    /**
     * Ensures that setMax() returns expected value
     *
     * @return void
     */
    public function testSetMax()
    {
        $validator = new File\Count(['min' => 1000, 'max' => 10000]);
        $validator->setMax(1000000);
        $this->assertEquals(1000000, $validator->getMax());

        $validator->setMin(100);
        $this->assertEquals(1000000, $validator->getMax());
    }

    public function testCanSetMaxValueUsingAnArrayWithMaxKey(): void
    {
        $validator   = new File\Count(['min' => 1000, 'max' => 10000]);
        $maxValue    = 33333333;
        $setMaxArray = ['max' => $maxValue];

        $validator->setMax($setMaxArray);
        $this->assertSame($maxValue, $validator->getMax());
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
     *
     * @return void
     */
    public function testSettingMaxWithInvalidArgumentRaisesException($max): void
    {
        $validator = new File\Count(['min' => 1000, 'max' => 10000]);
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid options to validator provided');

        $validator->setMax($max);
    }

    public function testCanSetMinUsingAnArrayWithAMinKey(): void
    {
        $validator   = new File\Count(['min' => 1000, 'max' => 10000]);
        $minValue    = 33;
        $setMinArray = ['min' => $minValue];

        $validator->setMin($setMinArray);
        $this->assertEquals($minValue, $validator->getMin());
    }

    /**
     * @dataProvider invalidMinMaxValues
     *
     * @return void
     */
    public function testSettingMinWithInvalidArgumentRaisesException($min): void
    {
        $validator = new File\Count(['min' => 1000, 'max' => 10000]);
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid options to validator provided');
        $validator->setMin($min);
    }

    public function testThrowErrorReturnsFalseAndSetsMessageWhenProvidedWithArrayRepresentingTooFewFiles(): void
    {
        $validator = new File\Count(['min' => 1000, 'max' => 10000]);
        $filename  = 'test.txt';
        $fileArray = ['name' => $filename];

        $reflection = new ReflectionClass($validator);

        $method = $reflection->getMethod('throwError');
        $method->setAccessible(true);

        $property = $reflection->getProperty('value');
        $property->setAccessible(true);

        $result = $method->invoke($validator, $fileArray, File\Count::TOO_FEW);

        $this->assertFalse($result);
        $this->assertEquals($filename, $property->getValue($validator));
    }

    public function testThrowErrorReturnsFalseAndSetsMessageWhenProvidedWithASingleFilename(): void
    {
        $validator  = new File\Count(['min' => 1000, 'max' => 10000]);
        $filename   = 'test.txt';
        $reflection = new ReflectionClass($validator);

        $method = $reflection->getMethod('throwError');
        $method->setAccessible(true);

        $property = $reflection->getProperty('value');
        $property->setAccessible(true);

        $result = $method->invoke($validator, $filename, File\Count::TOO_FEW);

        $this->assertFalse($result);
        $this->assertEquals($filename, $property->getValue($validator));
    }

    public function testCanProvideMinAndMaxAsDiscreteConstructorArguments(): void
    {
        $min       = 1000;
        $max       = 10000;
        $validator = new File\Count($min, $max);

        $this->assertSame($min, $validator->getMin());
        $this->assertSame($max, $validator->getMax());
    }
}
