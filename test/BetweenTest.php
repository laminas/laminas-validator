<?php

declare(strict_types=1);

namespace LaminasTest\Validator;

use ArrayObject;
use Laminas\Validator\Between;
use Laminas\Validator\Exception\InvalidArgumentException;
use PHPUnit\Framework\TestCase;

use function array_keys;
use function implode;

/**
 * @group Laminas_Validator
 * @covers \Laminas\Validator\Between
 */
final class BetweenTest extends TestCase
{
    /**
     * @psalm-return array<string, array{
     *     min: int|string,
     *     max: int|string,
     *     inclusive: bool,
     *     expected: bool,
     *     value: int|float|string
     * }>
     */
    public function providerBasic(): array
    {
        return [
            'inclusive-int-valid-floor'              => [
                'min'       => 1,
                'max'       => 100,
                'inclusive' => true,
                'expected'  => true,
                'value'     => 1,
            ],
            'inclusive-int-valid-between'            => [
                'min'       => 1,
                'max'       => 100,
                'inclusive' => true,
                'expected'  => true,
                'value'     => 10,
            ],
            'inclusive-int-valid-ceiling'            => [
                'min'       => 1,
                'max'       => 100,
                'inclusive' => true,
                'expected'  => true,
                'value'     => 100,
            ],
            'inclusive-int-invaild-below'            => [
                'min'       => 1,
                'max'       => 100,
                'inclusive' => true,
                'expected'  => false,
                'value'     => 0,
            ],
            'inclusive-int-invalid-below-fractional' => [
                'min'       => 1,
                'max'       => 100,
                'inclusive' => true,
                'expected'  => false,
                'value'     => 0.99,
            ],
            'inclusive-int-invalid-above-fractional' => [
                'min'       => 1,
                'max'       => 100,
                'inclusive' => true,
                'expected'  => false,
                'value'     => 100.01,
            ],
            'inclusive-int-invalid-above'            => [
                'min'       => 1,
                'max'       => 100,
                'inclusive' => true,
                'expected'  => false,
                'value'     => 101,
            ],
            'exclusive-int-invalid-below'            => [
                'min'       => 1,
                'max'       => 100,
                'inclusive' => false,
                'expected'  => false,
                'value'     => 0,
            ],
            'exclusive-int-invalid-floor'            => [
                'min'       => 1,
                'max'       => 100,
                'inclusive' => false,
                'expected'  => false,
                'value'     => 1,
            ],
            'exclusive-int-invalid-ceiling'          => [
                'min'       => 1,
                'max'       => 100,
                'inclusive' => false,
                'expected'  => false,
                'value'     => 100,
            ],
            'exclusive-int-invalid-above'            => [
                'min'       => 1,
                'max'       => 100,
                'inclusive' => false,
                'expected'  => false,
                'value'     => 101,
            ],
            'inclusive-string-valid-floor'           => [
                'min'       => 'a',
                'max'       => 'z',
                'inclusive' => true,
                'expected'  => true,
                'value'     => 'a',
            ],
            'inclusive-string-valid-between'         => [
                'min'       => 'a',
                'max'       => 'z',
                'inclusive' => true,
                'expected'  => true,
                'value'     => 'm',
            ],
            'inclusive-string-valid-ceiling'         => [
                'min'       => 'a',
                'max'       => 'z',
                'inclusive' => true,
                'expected'  => true,
                'value'     => 'z',
            ],
            'exclusive-string-invalid-out-of-range'  => [
                'min'       => 'a',
                'max'       => 'z',
                'inclusive' => false,
                'expected'  => false,
                'value'     => '!',
            ],
            'exclusive-string-invalid-floor'         => [
                'min'       => 'a',
                'max'       => 'z',
                'inclusive' => false,
                'expected'  => false,
                'value'     => 'a',
            ],
            'exclusive-string-invalid-ceiling'       => [
                'min'       => 'a',
                'max'       => 'z',
                'inclusive' => false,
                'expected'  => false,
                'value'     => 'z',
            ],
            'inclusive-int-invalid-string'           => [
                'min'       => 0,
                'max'       => 99_999_999,
                'inclusive' => true,
                'expected'  => false,
                'value'     => 'asdasd',
            ],
            'inclusive-int-invalid-char'             => [
                'min'       => 0,
                'max'       => 99_999_999,
                'inclusive' => true,
                'expected'  => false,
                'value'     => 'q',
            ],
            'inclusive-string-invalid-zero'          => [
                'min'       => 'a',
                'max'       => 'zzzzz',
                'inclusive' => true,
                'expected'  => false,
                'value'     => 0,
            ],
            'inclusive-string-invalid-non-zero'      => [
                'min'       => 'a',
                'max'       => 'zzzzz',
                'inclusive' => true,
                'expected'  => false,
                'value'     => 10,
            ],
        ];
    }

    /**
     * Ensures that the validator follows expected behavior
     *
     * @dataProvider providerBasic
     * @param int|float|string $min
     * @param int|float|string $max
     */
    public function testBasic($min, $max, bool $inclusive, bool $expected, mixed $value): void
    {
        $validator = new Between(['min' => $min, 'max' => $max, 'inclusive' => $inclusive]);

        self::assertSame(
            $expected,
            $validator->isValid($value),
            'Failed value: ' . $value . ':' . implode("\n", $validator->getMessages())
        );
    }

    /**
     * Ensures that getMessages() returns expected default value
     */
    public function testGetMessages(): void
    {
        $validator = new Between(['min' => 1, 'max' => 10]);

        self::assertSame([], $validator->getMessages());
    }

    /**
     * Ensures that getMin() returns expected value
     */
    public function testGetMin(): void
    {
        $validator = new Between(['min' => 1, 'max' => 10]);

        self::assertSame(1, $validator->getMin());
    }

    /**
     * Ensures that getMax() returns expected value
     */
    public function testGetMax(): void
    {
        $validator = new Between(['min' => 1, 'max' => 10]);

        self::assertSame(10, $validator->getMax());
    }

    /**
     * Ensures that getInclusive() returns expected default value
     */
    public function testGetInclusive(): void
    {
        $validator = new Between(['min' => 1, 'max' => 10]);

        self::assertSame(true, $validator->getInclusive());
    }

    public function testEqualsMessageTemplates(): void
    {
        $validator = new Between(['min' => 1, 'max' => 10]);

        self::assertSame(
            [
                Between::NOT_BETWEEN,
                Between::NOT_BETWEEN_STRICT,
                Between::VALUE_NOT_NUMERIC,
                Between::VALUE_NOT_STRING,
            ],
            array_keys($validator->getMessageTemplates())
        );
        self::assertSame($validator->getOption('messageTemplates'), $validator->getMessageTemplates());
    }

    public function testEqualsMessageVariables(): void
    {
        $validator        = new Between(['min' => 1, 'max' => 10]);
        $messageVariables = [
            'min' => ['options' => 'min'],
            'max' => ['options' => 'max'],
        ];

        self::assertSame($messageVariables, $validator->getOption('messageVariables'));
        self::assertSame(array_keys($messageVariables), $validator->getMessageVariables());
    }

    /**
     * @covers \Laminas\Validator\Between::__construct()
     * @dataProvider constructBetweenValidatorInvalidDataProvider
     */
    public function testMissingMinOrMax(array $args): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Missing option: 'min' and 'max' have to be given");

        new Between($args);
    }

    /**
     * @psalm-return array<string, array{
     *     0: array<string, mixed>
     * }>
     */
    public function constructBetweenValidatorInvalidDataProvider(): array
    {
        return [
            'only-min'      => [['min' => 1]],
            'only-max'      => [['max' => 5]],
            'min-inclusive' => [['min' => 0, 'inclusive' => true]],
            'max-inclusive' => [['max' => 5, 'inclusive' => true]],
            'min-undefined' => [['min' => 0, 'foo' => 'bar']],
            'max-undefined' => [['max' => 5, 'foo' => 'bar']],
            'no-min-or-max' => [['bar' => 'foo', 'foo' => 'bar']],
        ];
    }

    public function testConstructorCanAcceptInclusiveParameter(): void
    {
        $validator = new Between(1, 10, false);

        self::assertFalse($validator->getInclusive());
    }

    public function testConstructWithTraversableOptions(): void
    {
        $options   = new ArrayObject(['min' => 1, 'max' => 10, 'inclusive' => false]);
        $validator = new Between($options);

        self::assertTrue($validator->isValid(5));
        self::assertFalse($validator->isValid(10));
    }

    public function testStringValidatedAgainstNumericMinAndMaxIsInvalidAndReturnsAFailureMessage(): void
    {
        $validator = new Between(['min' => 1, 'max' => 10]);

        self::assertFalse($validator->isValid('a'));

        $messages = $validator->getMessages();

        self::assertContains(
            'The min (\'1\') and max (\'10\') values are numeric, but the input is not',
            $messages
        );
    }

    public function testNumericValidatedAgainstStringMinAndMaxIsInvalidAndReturnsAFailureMessage(): void
    {
        $validator = new Between(['min' => 'a', 'max' => 'z']);

        self::assertFalse($validator->isValid(10));

        $messages = $validator->getMessages();

        self::assertContains(
            'The min (\'a\') and max (\'z\') values are non-numeric strings, but the input is not a string',
            $messages
        );
    }
}
