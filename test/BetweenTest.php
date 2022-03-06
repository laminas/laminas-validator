<?php

namespace LaminasTest\Validator;

use ArrayObject;
use Laminas\Validator\Between;
use Laminas\Validator\Exception\InvalidArgumentException;
use PHPUnit\Framework\TestCase;

use function array_keys;
use function implode;

/**
 * @group      Laminas_Validator
 */
class BetweenTest extends TestCase
{
    /**
     * @psalm-return array<string, array{
     *     min: int,
     *     max: int,
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
                'max'       => 99999999,
                'inclusive' => true,
                'expected'  => false,
                'value'     => 'asdasd',
            ],
            'inclusive-int-invalid-char'             => [
                'min'       => 0,
                'max'       => 99999999,
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
     * @param bool $inclusive
     * @param bool $expected
     * @param mixed $value
     * @return void
     */
    public function testBasic($min, $max, $inclusive, $expected, $value)
    {
        $validator = new Between(['min' => $min, 'max' => $max, 'inclusive' => $inclusive]);

        $this->assertSame(
            $expected,
            $validator->isValid($value),
            'Failed value: ' . $value . ':' . implode("\n", $validator->getMessages())
        );
    }

    /**
     * Ensures that getMessages() returns expected default value
     *
     * @return void
     */
    public function testGetMessages()
    {
        $validator = new Between(['min' => 1, 'max' => 10]);
        $this->assertEquals([], $validator->getMessages());
    }

    /**
     * Ensures that getMin() returns expected value
     *
     * @return void
     */
    public function testGetMin()
    {
        $validator = new Between(['min' => 1, 'max' => 10]);
        $this->assertEquals(1, $validator->getMin());
    }

    /**
     * Ensures that getMax() returns expected value
     *
     * @return void
     */
    public function testGetMax()
    {
        $validator = new Between(['min' => 1, 'max' => 10]);
        $this->assertEquals(10, $validator->getMax());
    }

    /**
     * Ensures that getInclusive() returns expected default value
     *
     * @return void
     */
    public function testGetInclusive()
    {
        $validator = new Between(['min' => 1, 'max' => 10]);
        $this->assertEquals(true, $validator->getInclusive());
    }

    public function testEqualsMessageTemplates(): void
    {
        $validator = new Between(['min' => 1, 'max' => 10]);
        $this->assertSame(
            [
                Between::NOT_BETWEEN,
                Between::NOT_BETWEEN_STRICT,
                Between::VALUE_NOT_NUMERIC,
                Between::VALUE_NOT_STRING,
            ],
            array_keys($validator->getMessageTemplates())
        );
        $this->assertEquals($validator->getOption('messageTemplates'), $validator->getMessageTemplates());
    }

    public function testEqualsMessageVariables(): void
    {
        $validator        = new Between(['min' => 1, 'max' => 10]);
        $messageVariables = [
            'min' => ['options' => 'min'],
            'max' => ['options' => 'max'],
        ];
        $this->assertSame($messageVariables, $validator->getOption('messageVariables'));
        $this->assertEquals(array_keys($messageVariables), $validator->getMessageVariables());
    }

    /**
     * @covers \Laminas\Validator\Between::__construct()
     * @dataProvider constructBetweenValidatorInvalidDataProvider
     * @param array $args
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
        $this->assertFalse($validator->getInclusive());
    }

    public function testConstructWithTraversableOptions(): void
    {
        $options   = new ArrayObject(['min' => 1, 'max' => 10, 'inclusive' => false]);
        $validator = new Between($options);

        $this->assertTrue($validator->isValid(5));
        $this->assertFalse($validator->isValid(10));
    }

    public function testStringValidatedAgainstNumericMinAndMaxIsInvalidAndReturnsAFailureMessage(): void
    {
        $validator = new Between(['min' => 1, 'max' => 10]);
        $this->assertFalse($validator->isValid('a'));
        $messages = $validator->getMessages();
        $this->assertContains(
            'The min (\'1\') and max (\'10\') values are numeric, but the input is not',
            $messages
        );
    }

    public function testNumericValidatedAgainstStringMinAndMaxIsInvalidAndReturnsAFailureMessage(): void
    {
        $validator = new Between(['min' => 'a', 'max' => 'z']);
        $this->assertFalse($validator->isValid(10));
        $messages = $validator->getMessages();
        $this->assertContains(
            'The min (\'a\') and max (\'z\') values are non-numeric strings, but the input is not a string',
            $messages
        );
    }

    public function testBetweenConstructorGivenArgumentsInsteadOfOptionsArray(): void
    {
        $validator = new Between(1, 4, true);

        $this->assertTrue($validator->isValid(2));
    }
}
