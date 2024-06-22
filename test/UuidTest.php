<?php

declare(strict_types=1);

namespace LaminasTest\Validator;

use Laminas\Validator\Uuid;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use stdClass;

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
     * @psalm-return array<string, array{0: string}>
     */
    public static function validUuidProvider(): array
    {
        return [
            'zero-fill' => ['00000000-0000-0000-0000-000000000000'],
            'version-1' => ['74f941b0-3083-11ef-9a10-ee9df49b771c'],
            'version-2' => ['00000014-3083-21ef-bd01-ee9df49b771c'],
            'version-3' => ['9bbcc896-cd1f-3f0c-869b-d0e039ed363d'],
            'version-4' => ['b2e6a6ac-5efe-4f45-8210-147ff417da2e'],
            'version-5' => ['de560e31-bef7-589b-82b6-449f06ee38db'],
            'version-6' => ['1ef30837-4fa1-6b12-995a-ee9df49b771c'],
            'version-7' => ['01903f85-fa15-7095-ab0a-e250c9310140'],
            'uppercase' => ['FF6F8CB0-C57D-11E1-9B21-0800200C9A66'],
        ];
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
