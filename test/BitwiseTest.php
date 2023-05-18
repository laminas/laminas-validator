<?php

declare(strict_types=1);

namespace LaminasTest\Validator;

use ArrayObject;
use Laminas\Validator\Bitwise;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class BitwiseTest extends TestCase
{
    #[DataProvider('constructDataProvider')]
    public function testConstruct(array $args, array $options): void
    {
        $validator = new Bitwise($args);

        self::assertSame($options['control'], $validator->getControl());
        self::assertSame($options['operator'], $validator->getOperator());
        self::assertSame($options['strict'], $validator->getStrict());
    }

    #[DataProvider('constructDataProvider')]
    public function testConstructWithTraversableOptions(array $args, array $options): void
    {
        $validator = new Bitwise(
            new ArrayObject($args)
        );

        self::assertSame($options['control'], $validator->getControl());
        self::assertSame($options['operator'], $validator->getOperator());
        self::assertSame($options['strict'], $validator->getStrict());
    }

    /**
     * @psalm-return array<array-key, array{
     *     0: array,
     *     1: array<string, mixed>
     * }>
     */
    public static function constructDataProvider(): array
    {
        return [
            [
                [],
                ['control' => null, 'operator' => null, 'strict' => false],
            ],
            [
                ['control' => 0x1],
                ['control' => 0x1, 'operator' => null, 'strict' => false],
            ],
            [
                ['control' => 0x1, 'operator' => Bitwise::OP_AND],
                ['control' => 0x1, 'operator' => Bitwise::OP_AND, 'strict' => false],
            ],
            [
                ['control' => 0x1, 'operator' => Bitwise::OP_AND, 'strict' => true],
                ['control' => 0x1, 'operator' => Bitwise::OP_AND, 'strict' => true],
            ],
        ];
    }

    public function testBitwiseAndNotStrict(): void
    {
        $controlSum = 0x7; // (0x1 | 0x2 | 0x4) === 0x7

        $validator = new Bitwise();
        $validator->setControl($controlSum);
        $validator->setOperator(Bitwise::OP_AND);

        self::assertTrue($validator->isValid(0x1));
        self::assertTrue($validator->isValid(0x2));
        self::assertTrue($validator->isValid(0x4));
        self::assertFalse($validator->isValid(0x8));

        $validator->isValid(0x8);
        $messages = $validator->getMessages();

        self::assertArrayHasKey($validator::NOT_AND, $messages);
        self::assertSame("The input has no common bit set with '$controlSum'", $messages[$validator::NOT_AND]);

        self::assertTrue($validator->isValid(0x1 | 0x2));
        self::assertTrue($validator->isValid(0x1 | 0x2 | 0x4));
        self::assertTrue($validator->isValid(0x1 | 0x8));
    }

    public function testBitwiseAndStrict(): void
    {
        $controlSum = 0x7; // (0x1 | 0x2 | 0x4) === 0x7

        $validator = new Bitwise();
        $validator->setControl($controlSum);
        $validator->setOperator(Bitwise::OP_AND);
        $validator->setStrict(true);

        self::assertTrue($validator->isValid(0x1));
        self::assertTrue($validator->isValid(0x2));
        self::assertTrue($validator->isValid(0x4));
        self::assertFalse($validator->isValid(0x8));

        $validator->isValid(0x8);
        $messages = $validator->getMessages();

        self::assertArrayHasKey($validator::NOT_AND_STRICT, $messages);
        self::assertSame(
            "The input doesn't have the same bits set as '$controlSum'",
            $messages[$validator::NOT_AND_STRICT]
        );

        self::assertTrue($validator->isValid(0x1 | 0x2));
        self::assertTrue($validator->isValid(0x1 | 0x2 | 0x4));
        self::assertFalse($validator->isValid(0x1 | 0x8));
    }

    /**
     * @psalm-return array<array-key, array{
     *     0: int,
     *     1: bool,
     *     2: array<string, string>
     * }>
     */
    public static function bitwiseXorProvider(): array
    {
        return [
            [0x2, true, []],
            [0x8, true, []],
            [0x10, true, []],
            [0x1, false, [Bitwise::NOT_XOR => "The input has common bit set with '5'"]],
            [0x4, false, [Bitwise::NOT_XOR => "The input has common bit set with '5'"]],
            [0x8 | 0x10, true, []],
            [0x1 | 0x4, false, [Bitwise::NOT_XOR => "The input has common bit set with '5'"]],
            [0x1 | 0x8, false, [Bitwise::NOT_XOR => "The input has common bit set with '5'"]],
            [0x4 | 0x8, false, [Bitwise::NOT_XOR => "The input has common bit set with '5'"]],
        ];
    }

    #[DataProvider('bitwiseXorProvider')]
    public function testBitwiseXor(int $value, bool $expected, array $expectedMessages): void
    {
        $controlSum = 0x5; // (0x1 | 0x4) === 0x5
        $validator  = new Bitwise();
        $validator->setControl($controlSum);
        $validator->setOperator(Bitwise::OP_XOR);

        self::assertSame($expected, $validator->isValid($value));
        self::assertSame($expectedMessages, $validator->getMessages());

        /*
        self::assertTrue($validator->isValid(0x2));
        self::assertTrue($validator->isValid(0x8));
        self::assertTrue($validator->isValid(0x10));
        self::assertFalse($validator->isValid(0x1));
        self::assertFalse($validator->isValid(0x4));

        $validator->isValid(0x4);
        $messages = $validator->getMessages();
        self::assertArrayHasKey($validator::NOT_XOR, $messages);
        self::assertSame("The input has common bit set with '$controlSum'", $messages[$validator::NOT_XOR]);

        self::assertTrue($validator->isValid(0x8 | 0x10));
        self::assertFalse($validator->isValid(0x1 | 0x4));
        self::assertFalse($validator->isValid(0x1 | 0x8));
        self::assertFalse($validator->isValid(0x4 | 0x8));
         */
    }

    public function testSetOperator(): void
    {
        $validator = new Bitwise();

        $validator->setOperator(Bitwise::OP_AND);

        self::assertSame(Bitwise::OP_AND, $validator->getOperator());

        $validator->setOperator(Bitwise::OP_XOR);

        self::assertSame(Bitwise::OP_XOR, $validator->getOperator());
    }

    public function testSetStrict(): void
    {
        $validator = new Bitwise();

        self::assertFalse($validator->getStrict(), 'Strict false by default');

        $validator->setStrict(false);
        self::assertFalse($validator->getStrict());

        $validator->setStrict(true);
        self::assertTrue($validator->getStrict());

        $validator = new Bitwise(0x1, Bitwise::OP_AND, false);
        self::assertFalse($validator->getStrict());

        $validator = new Bitwise(0x1, Bitwise::OP_AND, true);
        self::assertTrue($validator->getStrict());
    }

    public function testConstructorCanAcceptAllOptionsAsDiscreteArguments(): void
    {
        $control  = 0x1;
        $operator = Bitwise::OP_AND;
        $strict   = true;

        $validator = new Bitwise($control, $operator, $strict);

        self::assertSame($control, $validator->getControl());
        self::assertSame($operator, $validator->getOperator());
        self::assertSame($strict, $validator->getStrict());
    }

    public function testCanRetrieveControlValue(): void
    {
        $control   = 0x1;
        $validator = new Bitwise($control, Bitwise::OP_AND, false);

        self::assertSame($control, $validator->getControl());
    }

    public function testCanRetrieveOperatorValue(): void
    {
        $operator  = Bitwise::OP_AND;
        $validator = new Bitwise(0x1, $operator, false);

        self::assertSame($operator, $validator->getOperator());
    }

    public function testCanRetrieveStrictValue(): void
    {
        $strict    = true;
        $validator = new Bitwise(0x1, Bitwise::OP_AND, $strict);

        self::assertSame($strict, $validator->getStrict());
    }

    public function testIsValidReturnsFalseWithInvalidOperator(): void
    {
        $validator      = new Bitwise(0x1, 'or', false);
        $expectedResult = false;

        self::assertSame($expectedResult, $validator->isValid(0x2));
    }

    public function testCanSetControlValue(): void
    {
        $validator = new Bitwise();
        $control   = 0x2;
        $validator->setControl($control);

        self::assertSame($control, $validator->getControl());
    }
}
