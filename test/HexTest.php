<?php

declare(strict_types=1);

namespace LaminasTest\Validator;

use Laminas\Validator\Hex;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use function array_keys;

final class HexTest extends TestCase
{
    private Hex $validator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = new Hex();
    }

    /**
     * Ensures that the validator follows expected behavior
     *
     * @param int|string $input
     */
    #[DataProvider('basicDataProvider')]
    public function testBasic(mixed $input, bool $expected, string|null $errorKey): void
    {
        self::assertSame($expected, $this->validator->isValid($input));
        if ($errorKey === null) {
            return;
        }

        self::assertArrayHasKey($errorKey, $this->validator->getMessages());
    }

    /**
     * @psalm-return array<string, array{
     *     0: mixed,
     *     1: bool,
     *     2: null|string,
     * }>
     */
    public static function basicDataProvider(): array
    {
        return [
            'valid; int; 1'                   => [1, true, null],
            'valid; hex; 0x1'                 => [0x1, true, null],
            'valid; hex; 0x123'               => [0x123, true, null],
            'valid; string; 1'                => ['1', true, null],
            'valid; string; abc123'           => ['abc123', true, null],
            'valid; string; ABC123'           => ['ABC123', true, null],
            'valid; string; 1234567890abcdef' => ['1234567890abcdef', true, null],
            'invalid; string; g'              => ['g', false, Hex::NOT_HEX],
            'invalid; string; 1.2'            => ['1.2', false, Hex::NOT_HEX],
            'invalid; null'                   => [null, false, Hex::INVALID],
            'invalid; empty string'           => ['', false, Hex::NOT_HEX],
            'invalid; array'                  => [['abc123'], false, Hex::INVALID],
            'invalid; float'                  => [1.4, false, Hex::INVALID],
            'invalid; hex colour string'      => ['#ffffff', false, Hex::NOT_HEX],
        ];
    }

    /**
     * Ensures that getMessages() returns expected default value
     */
    public function testGetMessages(): void
    {
        self::assertSame([], $this->validator->getMessages());
    }

    public function testEqualsMessageTemplates(): void
    {
        self::assertSame(
            [
                Hex::INVALID,
                Hex::NOT_HEX,
            ],
            array_keys($this->validator->getMessageTemplates())
        );
        self::assertSame($this->validator->getOption('messageTemplates'), $this->validator->getMessageTemplates());
    }
}
