<?php

declare(strict_types=1);

namespace LaminasTest\Validator;

use Laminas\Validator\IsArray;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(IsArray::class)]
class IsArrayTest extends TestCase
{
    /** @return array<string, array{0: mixed}> */
    public static function invalidValueProvider(): array
    {
        return [
            'String'  => ['foo'],
            'Integer' => [123],
            'Float'   => [4.2],
            'Object'  => [(object) ['foo' => 'bar']],
            'Boolean' => [true],
            'Null'    => [null],
        ];
    }

    #[DataProvider('invalidValueProvider')]
    public function testInvalidValuesAreDeemedInvalid(mixed $input): void
    {
        $validator = new IsArray();
        self::assertFalse($validator->isValid($input));
    }

    public function testThatAnArrayIsConsideredValid(): void
    {
        self::assertTrue(
            (new IsArray())->isValid(['whatever']),
        );
    }

    public function testThatTheInvalidTypeIsPresentInTheErrorMessage(): void
    {
        $validator = new IsArray();
        self::assertFalse($validator->isValid('Foo'));
        $messages = $validator->getMessages();

        self::assertArrayHasKey(IsArray::NOT_ARRAY, $messages);

        self::assertEquals(
            'Expected an array value but string provided',
            $messages[IsArray::NOT_ARRAY]
        );
    }
}
