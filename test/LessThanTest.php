<?php

declare(strict_types=1);

namespace LaminasTest\Validator;

use Laminas\Validator\LessThan;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use function array_keys;

final class LessThanTest extends TestCase
{
    /**
     * Ensures that the validator follows expected behavior
     *
     * @param int|string $input
     */
    #[DataProvider('basicDataProvider')]
    public function testBasic(array $options, $input, bool $expected): void
    {
        $validator = new LessThan(...$options);

        self::assertSame($expected, $validator->isValid($input));
    }

    /**
     * @psalm-return array<string, array{
     *     0: array,
     *     1: mixed,
     *     2: bool
     * }>
     */
    public static function basicDataProvider(): array
    {
        return [
            // phpcs:disable
            'valid; non inclusive; 100 > -1'     => [[100], -1,     true],
            'valid; non inclusive; 100 > 0'      => [[100], 0,      true],
            'valid; non inclusive; 100 > 0.01'   => [[100], 0.01,   true],
            'valid; non inclusive; 100 > 1'      => [[100], 1,      true],
            'valid; non inclusive; 100 > 99.999' => [[100], 99.999, true],

            'invalid; non inclusive; 100 <= 100'    => [[100], 100,    false],
            'invalid; non inclusive; 100 <= 100.0'  => [[100], 100.0,  false],
            'invalid; non inclusive; 100 <= 100.01' => [[100], 100.01, false],

            'valid; inclusive; 100 >= -1'     => [[100, true], -1,     true],
            'valid; inclusive; 100 >= 0'      => [[100, true], 0,      true],
            'valid; inclusive; 100 >= 0.01'   => [[100, true], 0.01,   true],
            'valid; inclusive; 100 >= 1'      => [[100, true], 1,      true],
            'valid; inclusive; 100 >= 99.999' => [[100, true], 99.999, true],
            'valid; inclusive; 100 >= 100'    => [[100, true], 100,    true],
            'valid; inclusive; 100 >= 100.0'  => [[100, true], 100.0,  true],

            'invalid; inclusive; 100 < 100.01' => [[100, true], 100.01, false],

            'invalid; non inclusive; a >= a' => [['a'], 'a', false],
            'invalid; non inclusive; a >= b' => [['a'], 'b', false],
            'invalid; non inclusive; a >= c' => [['a'], 'c', false],
            'invalid; non inclusive; a >= d' => [['a'], 'd', false],

            'valid; inclusive; a <= a' => [['a', true], 'a', true],

            'valid; non inclusive; z > x' => [['z'], 'x', true],
            'valid; non inclusive; z > y' => [['z'], 'y', true],

            'valid; inclusive; z >= x' => [['z', true], 'x', true],
            'valid; inclusive; z >= y' => [['z', true], 'y', true],
            'valid; inclusive; z >= z' => [['z', true], 'z', true],

            'valid; inclusive; 100 >= -1; array'     => [[['max' => 100, 'inclusive' => true]], -1,     true],
            'valid; inclusive; 100 >= 0; array'      => [[['max' => 100, 'inclusive' => true]], 0,      true],
            'valid; inclusive; 100 >= 0.01; array'   => [[['max' => 100, 'inclusive' => true]], 0.01,   true],
            'valid; inclusive; 100 >= 1; array'      => [[['max' => 100, 'inclusive' => true]], 1,      true],
            'valid; inclusive; 100 >= 99.999; array' => [[['max' => 100, 'inclusive' => true]], 99.999, true],
            'valid; inclusive; 100 >= 100; array'    => [[['max' => 100, 'inclusive' => true]], 100,    true],
            'valid; inclusive; 100 >= 100.0; array'  => [[['max' => 100, 'inclusive' => true]], 100.0,  true],

            'invalid; inclusive; 100 < 100.01; array' => [[['max' => 100, 'inclusive' => true]],  100.01, false],

            'valid; non inclusive; 100 > -1; array'     => [[['max' => 100, 'inclusive' => false]], -1,     true],
            'valid; non inclusive; 100 > 0; array'      => [[['max' => 100, 'inclusive' => false]], 0,      true],
            'valid; non inclusive; 100 > 0.01; array'   => [[['max' => 100, 'inclusive' => false]], 0.01,   true],
            'valid; non inclusive; 100 > 1; array'      => [[['max' => 100, 'inclusive' => false]], 1,      true],
            'valid; non inclusive; 100 > 99.999; array' => [[['max' => 100, 'inclusive' => false]], 99.999, true],

            'invalid; non inclusive; 100 <= 100; array'    => [[['max' => 100, 'inclusive' => false]], 100,    false],
            'invalid; non inclusive; 100 <= 100.0; array'  => [[['max' => 100, 'inclusive' => false]], 100.0,  false],
            'invalid; non inclusive; 100 <= 100.01; array' => [[['max' => 100, 'inclusive' => false]], 100.01, false],
            // phpcs:enable
        ];
    }

    /**
     * Ensures that getMessages() returns expected default value
     */
    public function testGetMessages(): void
    {
        $validator = new LessThan(10);

        self::assertSame([], $validator->getMessages());
    }

    /**
     * Ensures that getMax() returns expected value
     */
    public function testGetMax(): void
    {
        $validator = new LessThan(10);

        self::assertSame(10, $validator->getMax());
    }

    /**
     * Ensures that getInclusive() returns expected default value
     */
    public function testGetInclusive(): void
    {
        $validator = new LessThan(10);

        self::assertSame(false, $validator->getInclusive());
    }

    public function testEqualsMessageTemplates(): void
    {
        $validator = new LessThan(10);

        self::assertSame(
            [
                LessThan::NOT_LESS,
                LessThan::NOT_LESS_INCLUSIVE,
            ],
            array_keys($validator->getMessageTemplates())
        );
        self::assertSame($validator->getOption('messageTemplates'), $validator->getMessageTemplates());
    }

    public function testEqualsMessageVariables(): void
    {
        $validator        = new LessThan(10);
        $messageVariables = [
            'max' => 'max',
        ];

        self::assertSame($messageVariables, $validator->getOption('messageVariables'));
        self::assertSame(array_keys($messageVariables), $validator->getMessageVariables());
    }

    public function testConstructorAllowsSettingAllOptionsAsDiscreteArguments(): void
    {
        $validator = new LessThan(10, true);

        self::assertSame(10, $validator->getMax());
        self::assertTrue($validator->getInclusive());
    }
}
