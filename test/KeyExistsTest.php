<?php

declare(strict_types=1);

namespace LaminasTest\Validator;

use ArrayObject;
use Laminas\Validator\Exception\InvalidArgumentException;
use Laminas\Validator\KeyExists;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use SplPriorityQueue;

final class KeyExistsTest extends TestCase
{
    /** @return list<array{0: mixed, 1: int|string, 2: bool, 3: string|null}> */
    public static function basicDataProvider(): array
    {
        return [
            [
                'fred',
                'foo',
                false,
                KeyExists::ERR_NOT_ITERABLE,
            ],
            [
                null,
                'foo',
                false,
                KeyExists::ERR_NOT_ITERABLE,
            ],
            [
                1,
                'foo',
                false,
                KeyExists::ERR_NOT_ITERABLE,
            ],
            [
                true,
                'foo',
                false,
                KeyExists::ERR_NOT_ITERABLE,
            ],
            [
                [],
                'foo',
                false,
                KeyExists::ERR_KEY_NOT_FOUND,
            ],
            [
                ['bar' => 'baz'],
                'foo',
                false,
                KeyExists::ERR_KEY_NOT_FOUND,
            ],
            [
                ['foo' => 'baz'],
                'foo',
                true,
                null,
            ],
            [
                ['5' => 'foo'],
                '5',
                true,
                null,
            ],
            [
                ['5' => 'foo'],
                5,
                true,
                null,
            ],
            [
                [5 => 'foo'],
                '5',
                true,
                null,
            ],
            [
                [5 => 'foo'],
                5,
                true,
                null,
            ],
            [
                ['a', 'b', 'c', 'd'],
                2,
                true,
                null,
            ],
            [
                ['a', 'b', 'c', 'd'],
                5,
                false,
                KeyExists::ERR_KEY_NOT_FOUND,
            ],
            [
                new ArrayObject([]),
                'foo',
                false,
                KeyExists::ERR_KEY_NOT_FOUND,
            ],
            [
                new ArrayObject(['bar' => 'baz']),
                'foo',
                false,
                KeyExists::ERR_KEY_NOT_FOUND,
            ],
            [
                new ArrayObject(['foo' => 'baz']),
                'foo',
                true,
                null,
            ],
            [
                new ArrayObject(['5' => 'foo']),
                '5',
                true,
                null,
            ],
            [
                new ArrayObject(['5' => 'foo']),
                5,
                true,
                null,
            ],
            [
                new ArrayObject([5 => 'foo']),
                '5',
                true,
                null,
            ],
            [
                new ArrayObject([5 => 'foo']),
                5,
                true,
                null,
            ],
            [
                new ArrayObject(['a', 'b', 'c', 'd']),
                2,
                true,
                null,
            ],
            [
                new ArrayObject(['a', 'b', 'c', 'd']),
                5,
                false,
                KeyExists::ERR_KEY_NOT_FOUND,
            ],
            [
                ['' => 'hey'],
                '',
                true,
                null,
            ],
            [
                new ArrayObject(['' => 'hey']),
                '',
                true,
                null,
            ],
            [
                ['1.234' => 'hey'],
                '1.234',
                true,
                null,
            ],
            [
                new ArrayObject(['1.234' => 'hey']),
                '1.234',
                true,
                null,
            ],
        ];
    }

    #[DataProvider('basicDataProvider')]
    public function testBasicBehaviour(mixed $input, int|string $key, bool $valid, ?string $expectError): void
    {
        $validator = new KeyExists(['key' => $key]);

        self::assertSame($valid, $validator->isValid($input));
        if ($expectError === null) {
            return;
        }

        $messages = $validator->getMessages();
        self::assertArrayHasKey($expectError, $messages);
    }

    /** @return list<array{0: mixed}> */
    public static function invalidKeyOptions(): array
    {
        return [
            [[]],
            [0.5],
            [true],
            [(object) ['foo' => 'bar']],
            [null],
        ];
    }

    #[DataProvider('invalidKeyOptions')]
    public function testInvalidKeyOptionIsExceptional(mixed $key): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The `key` option is required to be a string or integer');

        /** @psalm-suppress MixedArgumentTypeCoercion */
        new KeyExists(['key' => $key]);
    }

    public function testMissingKeyOptionIsExceptional(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The `key` option is required to be a string or integer');

        /** @psalm-suppress InvalidArgument */
        new KeyExists([]);
    }

    public function testExpectedKeyIsInterpolatedIntoMessages(): void
    {
        $validator = new KeyExists([
            'key'      => 'foo',
            'messages' => [
                KeyExists::ERR_KEY_NOT_FOUND => '"%key%" not found',
            ],
        ]);

        self::assertFalse($validator->isValid([]));
        $message = $validator->getMessages()[KeyExists::ERR_KEY_NOT_FOUND] ?? null;
        self::assertIsString($message);
        self::assertSame('"foo" not found', $message);
    }

    public function testTypeOfArgumentIsInterpolatedIntoMessages(): void
    {
        $validator = new KeyExists([
            'key'      => 'foo',
            'messages' => [
                KeyExists::ERR_NOT_ITERABLE => '"%type%" not good',
            ],
        ]);

        self::assertFalse($validator->isValid('baz'));
        $message = $validator->getMessages()[KeyExists::ERR_NOT_ITERABLE] ?? null;
        self::assertIsString($message);
        self::assertSame('"string" not good', $message);
    }

    public function testIterablesAreIteratedDuringValidation(): void
    {
        $input = new SplPriorityQueue();
        $input->insert('a', 0);
        $input->insert('b', 1);
        $input->insert('c', 2);

        self::assertCount(3, $input);

        $validator = new KeyExists([
            'key' => 2,
        ]);

        self::assertTrue($validator->isValid($input));

        self::assertCount(0, $input);
    }
}
