<?php

declare(strict_types=1);

namespace LaminasTest\Validator;

use Laminas\Validator\Bitwise;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class BitwiseTest extends TestCase
{
    /**
     * @return list<array{
     *     0: Bitwise::OP_AND|Bitwise::OP_XOR|null,
     *     1: bool,
     *     2: int,
     *     3: mixed,
     *     4: bool,
     *     5: string|null,
     * }>
     */
    public static function basicDataProvider(): array
    {
        return [
            [null, false, 0b0001, 0b0001, false, Bitwise::NO_OP],
            [Bitwise::OP_AND, false, 0b0001, 0b0001, true, null],
            [Bitwise::OP_AND, false, 0b0001, 1, true, null],
            [Bitwise::OP_AND, false, 0b0001, '1', true, null],
            [Bitwise::OP_AND, true,  0b0001, 1, true, null],
            [Bitwise::OP_AND, true,  0b0001, '1', true, null],
            [Bitwise::OP_AND, false, 0b0111, 0b0001, true, null],
            [Bitwise::OP_AND, true,  0b0001, 0b0011, false, Bitwise::NOT_AND_STRICT],
            [Bitwise::OP_AND, false, 0b0001, 0b0010, false, Bitwise::NOT_AND],
            [Bitwise::OP_AND, false, 0b0001, 'foo', false, Bitwise::NOT_INTEGER],
            [Bitwise::OP_AND, false, 0b0001, null, false, Bitwise::NOT_INTEGER],
            [Bitwise::OP_AND, false, 0b0001, 0.5, false, Bitwise::NOT_INTEGER],
            [Bitwise::OP_XOR, true,  0b0001, 0b0010, true, null],
            [Bitwise::OP_XOR, true,  0b0001, 0b0100, true, null],
            [Bitwise::OP_XOR, true,  0b0111, 0b0100, false, Bitwise::NOT_XOR],
        ];
    }

    /**
     * @param Bitwise::OP_AND|Bitwise::OP_XOR|null $operator
     */
    #[DataProvider('basicDataProvider')]
    public function testBasic(
        ?string $operator,
        bool $strict,
        int $control,
        mixed $input,
        bool $valid,
        string|null $errorKey,
    ): void {
        $validator = new Bitwise([
            'operator' => $operator,
            'strict'   => $strict,
            'control'  => $control,
        ]);

        self::assertSame($valid, $validator->isValid($input));

        if ($errorKey !== null) {
            self::assertArrayHasKey($errorKey, $validator->getMessages());
        }
    }
}
