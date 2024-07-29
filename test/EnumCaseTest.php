<?php

declare(strict_types=1);

namespace LaminasTest\Validator;

use BackedEnum;
use Laminas\Validator\EnumCase;
use Laminas\Validator\Exception\InvalidArgumentException;
use LaminasTest\Validator\TestAsset\ExampleIntBackedEnum;
use LaminasTest\Validator\TestAsset\ExampleStringBackedEnum;
use LaminasTest\Validator\TestAsset\ExampleUnitEnum;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use UnitEnum;

class EnumCaseTest extends TestCase
{
    /**
     * @return iterable<int, array{
     *     0: class-string<BackedEnum>|class-string<UnitEnum>,
     *     1: mixed,
     *     2: bool,
     *     3: string|null,
     * }>
     */
    public static function basicProvider(): iterable
    {
        foreach (ExampleIntBackedEnum::cases() as $case) {
            yield [ExampleIntBackedEnum::class, $case->name, true, null];
            yield [ExampleIntBackedEnum::class, $case, false, EnumCase::ERR_INVALID_TYPE];
        }

        foreach (ExampleStringBackedEnum::cases() as $case) {
            yield [ExampleStringBackedEnum::class, $case->name, true, null];
            yield [ExampleStringBackedEnum::class, $case, false, EnumCase::ERR_INVALID_TYPE];
        }

        foreach (ExampleUnitEnum::cases() as $case) {
            yield [ExampleUnitEnum::class, $case->name, true, null];
            yield [ExampleUnitEnum::class, $case, false, EnumCase::ERR_INVALID_TYPE];
        }

        yield from [
            [ExampleStringBackedEnum::class, 'Not There', false, EnumCase::ERR_INVALID_VALUE],
            [ExampleStringBackedEnum::class, 10, false, EnumCase::ERR_INVALID_TYPE],
            [ExampleStringBackedEnum::class, ['foo'], false, EnumCase::ERR_INVALID_TYPE],
            [ExampleIntBackedEnum::class, 'Foo', false, EnumCase::ERR_INVALID_VALUE],
            [ExampleUnitEnum::class, 'Foo', false, EnumCase::ERR_INVALID_VALUE],
        ];
    }

    /** @param class-string<BackedEnum>|class-string<UnitEnum> $enum */
    #[DataProvider('basicProvider')]
    public function testBasic(string $enum, mixed $value, bool $expect, string|null $errorKey): void
    {
        $validator = new EnumCase(['enum' => $enum]);

        self::assertSame($expect, $validator->isValid($value));

        if ($errorKey === null) {
            return;
        }

        self::assertArrayHasKey($errorKey, $validator->getMessages());
    }

    public function testANonEnumClassOptionWillCauseAnException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected the `enum` option to be a unit or backed enum class-string');

        /** @psalm-suppress ArgumentTypeCoercion - Intentionally invalid value */
        new EnumCase(['enum' => 'Foo']);
    }

    public function testErrorMessageVariables(): void
    {
        $validator = new EnumCase(['enum' => ExampleStringBackedEnum::class]);

        self::assertFalse($validator->isValid([]));
        $message = $validator->getMessages()[EnumCase::ERR_INVALID_TYPE] ?? null;
        self::assertIsString($message);
        self::assertSame('Expected a string but received array', $message);

        self::assertFalse($validator->isValid('Not a Case'));
        $message = $validator->getMessages()[EnumCase::ERR_INVALID_VALUE] ?? null;
        self::assertIsString($message);
        self::assertSame('"Not a Case" is not a valid enum case', $message);
    }
}
