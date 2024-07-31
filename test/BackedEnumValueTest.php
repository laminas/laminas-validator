<?php

declare(strict_types=1);

namespace LaminasTest\Validator;

use BackedEnum;
use Laminas\Validator\BackedEnumValue;
use Laminas\Validator\Exception\InvalidArgumentException;
use LaminasTest\Validator\TestAsset\ExampleIntBackedEnum;
use LaminasTest\Validator\TestAsset\ExampleStringBackedEnum;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class BackedEnumValueTest extends TestCase
{
    /**
     * @return iterable<int, array{
     *     0: class-string<BackedEnum>,
     *     1: mixed,
     *     2: bool,
     *     3: string|null,
     * }>
     */
    public static function basicProvider(): iterable
    {
        foreach (ExampleIntBackedEnum::cases() as $case) {
            yield [ExampleIntBackedEnum::class, $case->value, true, null];
            yield [ExampleIntBackedEnum::class, $case, false, BackedEnumValue::ERR_INVALID_TYPE];
        }

        foreach (ExampleIntBackedEnum::cases() as $case) {
            yield [ExampleIntBackedEnum::class, (string) $case->value, true, null];
        }

        foreach (ExampleStringBackedEnum::cases() as $case) {
            yield [ExampleStringBackedEnum::class, $case->value, true, null];
            yield [ExampleStringBackedEnum::class, $case, false, BackedEnumValue::ERR_INVALID_TYPE];
        }

        yield from [
            [ExampleStringBackedEnum::class, 'Not There', false, BackedEnumValue::ERR_INVALID_VALUE],
            [ExampleStringBackedEnum::class, 10, false, BackedEnumValue::ERR_INVALID_TYPE],
            [ExampleStringBackedEnum::class, ['foo'], false, BackedEnumValue::ERR_INVALID_TYPE],
            [ExampleIntBackedEnum::class, 123, false, BackedEnumValue::ERR_INVALID_VALUE],
            [ExampleIntBackedEnum::class, '123', false, BackedEnumValue::ERR_INVALID_VALUE],
            [ExampleIntBackedEnum::class, ['foo'], false, BackedEnumValue::ERR_INVALID_TYPE],
            [ExampleIntBackedEnum::class, 'foo', false, BackedEnumValue::ERR_INVALID_TYPE],
        ];
    }

    /** @param class-string<BackedEnum> $enum */
    #[DataProvider('basicProvider')]
    public function testBasic(string $enum, mixed $value, bool $expect, string|null $errorKey): void
    {
        $validator = new BackedEnumValue(['enum' => $enum]);

        self::assertSame($expect, $validator->isValid($value));

        if ($errorKey === null) {
            return;
        }

        self::assertArrayHasKey($errorKey, $validator->getMessages());
    }

    public function testANonEnumClassOptionWillCauseAnException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected the `enum` option to be a backed enum class-string');

        /** @psalm-suppress ArgumentTypeCoercion - Intentionally invalid value */
        new BackedEnumValue(['enum' => 'Foo']);
    }

    public function testErrorMessageVariables(): void
    {
        $validator = new BackedEnumValue(['enum' => ExampleStringBackedEnum::class]);

        self::assertFalse($validator->isValid([]));
        $message = $validator->getMessages()[BackedEnumValue::ERR_INVALID_TYPE] ?? null;
        self::assertIsString($message);
        self::assertSame('Expected a string or numeric value but received array', $message);

        self::assertFalse($validator->isValid('Not Valid'));
        $message = $validator->getMessages()[BackedEnumValue::ERR_INVALID_VALUE] ?? null;
        self::assertIsString($message);
        self::assertSame('"Not Valid" is not a valid enum case', $message);
    }
}
