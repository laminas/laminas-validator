<?php

declare(strict_types=1);

namespace LaminasTest\Validator;

use Laminas\Validator\LessThan;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use function array_keys;

/** @psalm-import-type OptionsArgument from LessThan */
final class LessThanTest extends TestCase
{
    /**
     * Ensures that the validator follows expected behavior
     *
     * @param OptionsArgument $options
     */
    #[DataProvider('basicDataProvider')]
    public function testBasic(array $options, mixed $input, bool $expected): void
    {
        $validator = new LessThan($options);

        self::assertSame($expected, $validator->isValid($input));
    }

    /**
     * @psalm-return array<string, array{
     *     0: OptionsArgument,
     *     1: mixed,
     *     2: bool
     * }>
     */
    public static function basicDataProvider(): array
    {
        return [
            'valid; non inclusive; 100 > -1'               => [['max' => 100, 'inclusive' => false], -1, true],
            'valid; non inclusive; 100 > 0'                => [['max' => 100, 'inclusive' => false], 0, true],
            'valid; non inclusive; 100 > 0.01'             => [['max' => 100, 'inclusive' => false], 0.01, true],
            'valid; non inclusive; 100 > 1'                => [['max' => 100, 'inclusive' => false], 1, true],
            'valid; non inclusive; 100 > 99.999'           => [['max' => 100, 'inclusive' => false], 99.999, true],
            'invalid; non inclusive; 100 <= 100'           => [['max' => 100, 'inclusive' => false], 100, false],
            'invalid; non inclusive; 100 <= 100.0'         => [['max' => 100, 'inclusive' => false], 100.0, false],
            'invalid; non inclusive; 100 <= 100.01'        => [['max' => 100, 'inclusive' => false], 100.01, false],
            'valid; inclusive; 100 >= -1'                  => [['max' => 100, 'inclusive' => true], -1, true],
            'valid; inclusive; 100 >= 0'                   => [['max' => 100, 'inclusive' => true], 0, true],
            'valid; inclusive; 100 >= 0.01'                => [['max' => 100, 'inclusive' => true], 0.01, true],
            'valid; inclusive; 100 >= 1'                   => [['max' => 100, 'inclusive' => true], 1, true],
            'valid; inclusive; 100 >= 99.999'              => [['max' => 100, 'inclusive' => true], 99.999, true],
            'valid; inclusive; 100 >= 100'                 => [['max' => 100, 'inclusive' => true], 100, true],
            'valid; inclusive; 100 >= 100.0'               => [['max' => 100, 'inclusive' => true], 100.0, true],
            'invalid; inclusive; 100 < 100.01'             => [['max' => 100.0, 'inclusive' => true], 100.01, false],
            'valid; inclusive; 100 >= -1; array'           => [['max' => 100, 'inclusive' => true], -1, true],
            'valid; inclusive; 100 >= 0; array'            => [['max' => 100, 'inclusive' => true], 0, true],
            'valid; inclusive; 100 >= 0.01; array'         => [['max' => 100, 'inclusive' => true], 0.01, true],
            'valid; inclusive; 100 >= 1; array'            => [['max' => 100, 'inclusive' => true], 1, true],
            'valid; inclusive; 100 >= 99.999; array'       => [['max' => 100, 'inclusive' => true], 99.999, true],
            'valid; inclusive; 100 >= 100; array'          => [['max' => 100, 'inclusive' => true], 100, true],
            'valid; inclusive; 100 >= 100.0; array'        => [['max' => 100, 'inclusive' => true], 100.0, true],
            'invalid; inclusive; 100 < 100.01; array'      => [['max' => 100, 'inclusive' => true], 100.01, false],
            'valid; non inclusive; 100 > -1; array'        => [['max' => 100, 'inclusive' => false], -1, true],
            'valid; non inclusive; 100 > 0; array'         => [['max' => 100, 'inclusive' => false], 0, true],
            'valid; non inclusive; 100 > 0.01; array'      => [['max' => 100, 'inclusive' => false], 0.01, true],
            'valid; non inclusive; 100 > 1; array'         => [['max' => 100, 'inclusive' => false], 1, true],
            'valid; non inclusive; 100 > 99.999; array'    => [['max' => 100, 'inclusive' => false], 99.999, true],
            'invalid; non inclusive; 100 <= 100; array'    => [['max' => 100, 'inclusive' => false], 100, false],
            'invalid; non inclusive; 100 <= 100.0; array'  => [['max' => 100, 'inclusive' => false], 100.0, false],
            'invalid; non inclusive; 100 <= 100.01; array' => [['max' => 100, 'inclusive' => false], 100.01, false],
            'valid; inclusive; numeric-string max'         => [['max' => '20', 'inclusive' => true], '15', true],
            'invalid; inclusive; numeric-string max'       => [['max' => '20', 'inclusive' => true], '25', false],
        ];
    }

    /**
     * Ensures that getMessages() returns expected default value
     */
    public function testGetMessages(): void
    {
        $validator = new LessThan(['max' => 10]);

        self::assertSame([], $validator->getMessages());
    }

    public function testEqualsMessageTemplates(): void
    {
        $validator = new LessThan(['max' => 10]);

        self::assertSame(
            [
                LessThan::NOT_LESS,
                LessThan::NOT_LESS_INCLUSIVE,
                LessThan::NOT_NUMERIC,
            ],
            array_keys($validator->getMessageTemplates()),
        );
        self::assertSame($validator->getOption('messageTemplates'), $validator->getMessageTemplates());
    }

    public function testEqualsMessageVariables(): void
    {
        $validator        = new LessThan(['max' => 10]);
        $messageVariables = [
            'max' => 'max',
        ];

        self::assertSame($messageVariables, $validator->getOption('messageVariables'));
        self::assertSame(array_keys($messageVariables), $validator->getMessageVariables());
    }

    /** @return list<array{0: mixed, 1: int, 2: bool, 3: string}> */
    public static function invalidValueProvider(): array
    {
        return [
            ['a', 10, true, LessThan::NOT_NUMERIC],
            ['foo', 10, true, LessThan::NOT_NUMERIC],
            [null, 10, true, LessThan::NOT_NUMERIC],
            ['', 10, true, LessThan::NOT_NUMERIC],
            [['foo'], 10, true, LessThan::NOT_NUMERIC],
            [[1], 10, true, LessThan::NOT_NUMERIC],
            ['11', 10, true, LessThan::NOT_LESS_INCLUSIVE],
            ['11', 10, false, LessThan::NOT_LESS],
            [11, 10, true, LessThan::NOT_LESS_INCLUSIVE],
            [11, 10, false, LessThan::NOT_LESS],
            [11.0, 10, true, LessThan::NOT_LESS_INCLUSIVE],
            [11.0, 10, false, LessThan::NOT_LESS],
        ];
    }

    #[DataProvider('invalidValueProvider')]
    public function testInvalidValues(mixed $value, int $max, bool $inclusive, string $expectError): void
    {
        $validator = new LessThan(['max' => $max, 'inclusive' => $inclusive]);
        self::assertFalse($validator->isValid($value));
        $messages = $validator->getMessages();
        self::assertArrayHasKey($expectError, $messages);
    }
}
