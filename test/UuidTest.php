<?php

declare(strict_types=1);

namespace LaminasTest\Validator;

use Generator;
use Laminas\Validator\Uuid;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid as UuidGen;
use stdClass;

use function sprintf;

final class UuidTest extends TestCase
{
    private Uuid $validator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = new Uuid();
    }

    #[DataProvider('validUuidProvider')]
    public function testValidUuid(string $uuid): void
    {
        self::assertTrue($this->validator->isValid($uuid));

        $messages = $this->validator->getMessages();

        self::assertCount(0, $messages);
    }

    #[DataProvider('invalidUuidProvider')]
    public function testInvalidUuid(mixed $uuid, string $expectedMessageKey): void
    {
        self::assertFalse($this->validator->isValid($uuid));

        $messages = $this->validator->getMessages();

        self::assertCount(1, $messages);
        self::assertArrayHasKey($expectedMessageKey, $messages);
        self::assertNotEmpty($messages[$expectedMessageKey]);
    }

    /**
     * @psalm-return Generator<string, array{0: string}>
     */
    public static function validUuidProvider(): Generator
    {
        yield 'zero-fill' => ['00000000-0000-0000-0000-000000000000'];

        for ($i = 0; $i < 10; $i++) {
            yield sprintf('v1-#%d', $i) => [UuidGen::uuid1()->toString()];
            yield sprintf('v2-#%d', $i) => [UuidGen::uuid2(1)->toString()];
            yield sprintf('v3-#%d', $i) => [UuidGen::uuid3(UuidGen::uuid4(), 'foo')->toString()];
            yield sprintf('v4-#%d', $i) => [UuidGen::uuid4()->toString()];
            yield sprintf('v5-#%d', $i) => [UuidGen::uuid5(UuidGen::uuid4(), 'foo')->toString()];
            yield sprintf('v6-#%d', $i) => [UuidGen::uuid6()->toString()];
            yield sprintf('v7-#%d', $i) => [UuidGen::uuid7()->toString()];
        }
    }

    /**
     * @psalm-return array<string, array{0: mixed, 1: string}>
     */
    public static function invalidUuidProvider(): array
    {
        return [
            'invalid-characters' => ['laminas6f8cb0-c57d-11e1-9b21-0800200c9a66', Uuid::INVALID],
            'missing-separators' => ['af6f8cb0c57d11e19b210800200c9a66', Uuid::INVALID],
            'invalid-segment-2'  => ['ff6f8cb0-c57da-51e1-9b21-0800200c9a66', Uuid::INVALID],
            'invalid-segment-1'  => ['af6f8cb-c57d-11e1-9b21-0800200c9a66', Uuid::INVALID],
            'invalid-segement-5' => ['3f6f8cb0-c57d-11e1-9b21-0800200c9a6', Uuid::INVALID],
            'truncated'          => ['3f6f8cb0', Uuid::INVALID],
            'empty-string'       => ['', Uuid::INVALID],
            'all-numeric'        => [123, Uuid::NOT_STRING],
            'object'             => [new stdClass(), Uuid::NOT_STRING],
        ];
    }
}
