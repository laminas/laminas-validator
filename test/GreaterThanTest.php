<?php

declare(strict_types=1);

namespace LaminasTest\Validator;

use Laminas\Validator\GreaterThan;
use PHPUnit\Framework\TestCase;

use function array_keys;

/**
 * @group Laminas_Validator
 * @covers \Laminas\Validator\GreaterThan
 */
final class GreaterThanTest extends TestCase
{
    /**
     * Ensures that the validator follows expected behavior
     *
     * @dataProvider basicDataProvider
     * @param int|string $input
     */
    public function testBasic(array $options, $input, bool $expected): void
    {
        $validator = new GreaterThan(...$options);

        self::assertSame($expected, $validator->isValid($input));
    }

    /**
     * @psalm-return array<string, array{
     *     0: array<array-key, mixed>,
     *     1: int|float|string,
     *     2: bool
     * }>
     */
    public function basicDataProvider(): array
    {
        return [
            // phpcs:disable
            'valid; non inclusive; 0 < 0.01' => [[0], 0.01, true],
            'valid; non inclusive; 0 < 1'    => [[0], 1,    true],
            'valid; non inclusive; 0 < 100'  => [[0], 100,  true],

            'invalid; non inclusive; 0 >= 0'     => [[0], 0,     false],
            'invalid; non inclusive; 0 >= 0.00'  => [[0], 0.00,  false],
            'invalid; non inclusive; 0 >= -0.01' => [[0], -0.01, false],
            'invalid; non inclusive; 0 >= -1'    => [[0], -1,    false],
            'invalid; non inclusive; 0 >= -100'  => [[0], -100,  false],

            'valid; inclusive; 0 <= 0'    => [[0, true], 0,    true],
            'valid; inclusive; 0 <= 0.00' => [[0, true], 0.00, true],
            'valid; inclusive; 0 <= 0.01' => [[0, true], 0.01, true],
            'valid; inclusive; 0 <= 1'    => [[0, true], 1,    true],
            'valid; inclusive; 0 <= 100'  => [[0, true], 100,  true],

            'invalid; inclusive; 0 >= -0.01' => [[0, true], -0.01, false],
            'invalid; inclusive; 0 >= -1'    => [[0, true], -1,    false],
            'invalid; inclusive; 0 >= -100'  => [[0, true], -100,  false],

            'valid; non inclusive; a < b'    => [['a'], 'b',    true],
            'valid; non inclusive; a < c'    => [['a'], 'c',    true],
            'valid; non inclusive; a < d'    => [['a'], 'd',    true],

            'valid; inclusive; a <= a' => [['a', true], 'a', true],
            'valid; inclusive; a <= b' => [['a', true], 'b', true],
            'valid; inclusive; a <= c' => [['a', true], 'c', true],
            'valid; inclusive; a <= d' => [['a', true], 'd', true],

            'invalid; non-inclusive; a >= a' => [['a', false], 'a', false],

            'invalid; non inclusive; z >= x'    => [['z'], 'x',    false],
            'invalid; non inclusive; z >= y'    => [['z'], 'y',    false],
            'invalid; non inclusive; z >= z'    => [['z'], 'z',    false],

            'invalid; inclusive; z > x' => [['z', true], 'x', false],
            'invalid; inclusive; z > y' => [['z', true], 'y', false],

            'valid; inclusive; 0 <= 0; array'    => [[['min' => 0, 'inclusive' => true]], 0,    true],
            'valid; inclusive; 0 <= 0.00; array' => [[['min' => 0, 'inclusive' => true]], 0.00, true],
            'valid; inclusive; 0 <= 0.01; array' => [[['min' => 0, 'inclusive' => true]], 0.01, true],
            'valid; inclusive; 0 <= 1; array'    => [[['min' => 0, 'inclusive' => true]], 1,    true],
            'valid; inclusive; 0 <= 100; array'  => [[['min' => 0, 'inclusive' => true]], 100,  true],

            'invalid; inclusive; 0 >= -0.01; array' => [[['min' => 0, 'inclusive' => true]], -0.01, false],
            'invalid; inclusive; 0 >= -1; array'    => [[['min' => 0, 'inclusive' => true]], -1,    false],
            'invalid; inclusive; 0 >= -100; array'  => [[['min' => 0, 'inclusive' => true]], -100,  false],

            'valid; non inclusive; 0 < 0.01; array' => [[['min' => 0, 'inclusive' => false]], 0.01, true],
            'valid; non inclusive; 0 < 1; array'    => [[['min' => 0, 'inclusive' => false]], 1,    true],
            'valid; non inclusive; 0 < 100; array'  => [[['min' => 0, 'inclusive' => false]], 100,  true],

            'invalid; non inclusive; 0 >= 0; array'     => [[['min' => 0, 'inclusive' => false]], 0,     false],
            'invalid; non inclusive; 0 >= 0.00; array'  => [[['min' => 0, 'inclusive' => false]], 0.00,  false],
            'invalid; non inclusive; 0 >= -0.01; array' => [[['min' => 0, 'inclusive' => false]], -0.01, false],
            'invalid; non inclusive; 0 >= -1; array'    => [[['min' => 0, 'inclusive' => false]], -1,    false],
            'invalid; non inclusive; 0 >= -100; array'  => [[['min' => 0, 'inclusive' => false]], -100,  false],
            // phpcs:enable
        ];
    }

    /**
     * Ensures that getMessages() returns expected default value
     */
    public function testGetMessages(): void
    {
        $validator = new GreaterThan(10);

        self::assertSame([], $validator->getMessages());
    }

    /**
     * Ensures that getMin() returns expected value
     */
    public function testGetMin(): void
    {
        $validator = new GreaterThan(10);

        self::assertSame(10, $validator->getMin());
    }

    /**
     * Ensures that getInclusive() returns expected default value
     */
    public function testGetInclusive(): void
    {
        $validator = new GreaterThan(10);

        self::assertFalse($validator->getInclusive());
    }

    public function testEqualsMessageTemplates(): void
    {
        $validator = new GreaterThan(1);

        self::assertSame(
            [
                GreaterThan::NOT_GREATER,
                GreaterThan::NOT_GREATER_INCLUSIVE,
            ],
            array_keys($validator->getMessageTemplates())
        );
        self::assertSame($validator->getOption('messageTemplates'), $validator->getMessageTemplates());
    }

    public function testEqualsMessageVariables(): void
    {
        $validator        = new GreaterThan(1);
        $messageVariables = [
            'min' => 'min',
        ];

        self::assertSame($messageVariables, $validator->getOption('messageVariables'));
        self::assertSame(array_keys($messageVariables), $validator->getMessageVariables());
    }

    /**
     * @dataProvider correctInclusiveMessageDataProvider
     */
    public function testCorrectInclusiveMessageReturn(float $input): void
    {
        $validator = new GreaterThan(10);
        $validator->isValid($input);
        $message = $validator->getMessages();

        self::assertArrayHaskey('notGreaterThan', $message);
        self::assertSame($message['notGreaterThan'], "The input is not greater than '10'");
    }

    /**
     * @psalm-return array{0: array{0: 0}, '0.5': array{0: float}, 5: array{0: 5}, 10: array{0: 10}}
     * @return (float|int)[][]
     */
    public function correctInclusiveMessageDataProvider(): array
    {
        return [
            '0'   => [0],
            '0.5' => [0.5],
            '5'   => [5],
            '10'  => [10],
        ];
    }

    /**
     * @dataProvider correctNotInclusiveMessageDataProvider
     */
    public function testCorrectNotInclusiveMessageReturn(float $input): void
    {
        $validator = new GreaterThan(['min' => 10, 'inclusive' => true]);
        $validator->isValid($input);
        $message = $validator->getMessages();

        self::assertArrayHaskey('notGreaterThanInclusive', $message);
        self::assertSame($message['notGreaterThanInclusive'], "The input is not greater than or equal to '10'");
    }

    /**
     * @psalm-return array{0: array{0: 0}, '0.5': array{0: float}, 5: array{0: 5}, 9: array{0: 9}}
     * @return (float|int)[][]
     */
    public function correctNotInclusiveMessageDataProvider(): array
    {
        return [
            '0'   => [0],
            '0.5' => [0.5],
            '5'   => [5],
            '9'   => [9],
        ];
    }

    public function testConstructorCanAcceptInclusiveFlagAsAnArgument(): void
    {
        $validator = new GreaterThan(10, true);

        self::assertTrue($validator->getInclusive());
    }
}
