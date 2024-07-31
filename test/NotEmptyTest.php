<?php

declare(strict_types=1);

namespace LaminasTest\Validator;

use Laminas\Validator\NotEmpty;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use stdClass;

/**
 * @psalm-import-type TypeArgument from NotEmpty
 */
final class NotEmptyTest extends TestCase
{
    private NotEmpty $validator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = new NotEmpty();
    }

    /**
     * Ensures that the validator follows expected behavior
     *
     * @param TypeArgument $types Array of type strings or constants
     * @param integer $expected Expected value of calculated type
     */
    #[DataProvider('constructorWithTypeArrayProvider')]
    public function testConstructorWithTypeArray(array|int|string $types, int $expected): void
    {
        $validator = new NotEmpty(['type' => $types]);

        $prop   = new ReflectionProperty($validator, 'type');
        $actual = $prop->getValue($validator);
        self::assertSame($expected, $actual);
    }

    /**
     * @psalm-return list<array{TypeArgument, int}>
     */
    public static function constructorWithTypeArrayProvider(): array
    {
        return [
            [['php', 'boolean'], NotEmpty::PHP],
            [['boolean', 'boolean'], NotEmpty::BOOLEAN],
            [[NotEmpty::PHP, NotEmpty::BOOLEAN], NotEmpty::PHP],
            [[NotEmpty::BOOLEAN, NotEmpty::BOOLEAN], NotEmpty::BOOLEAN],
            [NotEmpty::ALL, NotEmpty::ALL],
            ['boolean', NotEmpty::BOOLEAN],
        ];
    }

    #[DataProvider('basicProvider')]
    #[Group('Laminas-6708')]
    public function testBasic(mixed $value, bool $valid): void
    {
        $validator = new NotEmpty();

        self::assertSame($valid, $validator->isValid($value));
    }

    /**
     * Provides values and expected validity for the basic test
     *
     * @psalm-return array<array{scalar|list<int>|stdClass|null, bool}>
     */
    public static function basicProvider(): array
    {
        return [
            ['word', true],
            ['', false],
            ['    ', false],
            ['  word  ', true],
            ['0', true],
            [1, true],
            [0, true],
            [true, true],
            [false, false],
            [null, false],
            [[], false],
            [[5], true],
            [0.0, true],
            [1.0, true],
            [new stdClass(), true],
        ];
    }

    #[DataProvider('booleanProvider')]
    public function testOnlyBoolean(mixed $value, bool $valid): void
    {
        $validator = new NotEmpty(['type' => NotEmpty::BOOLEAN]);
        self::assertSame($valid, $validator->isValid($value));
    }

    /**
     * Provides values and their expected validity for boolean empty
     *
     * @psalm-return array<array{scalar|list<string>|null, bool}>
     */
    public static function booleanProvider(): array
    {
        return [
            [false, false],
            [true, true],
            [0, true],
            [1, true],
            [0.0, true],
            [1.0, true],
            ['', true],
            ['abc', true],
            ['0', true],
            ['1', true],
            [[], true],
            [['xxx'], true],
            [null, true],
        ];
    }

    #[DataProvider('integerOnlyProvider')]
    public function testOnlyInteger(mixed $value, bool $valid): void
    {
        $validator = new NotEmpty(['type' => NotEmpty::INTEGER]);
        self::assertSame($valid, $validator->isValid($value));
    }

    /**
     * Provides values and their expected validity for when the validator is testing empty integer values
     *
     * @psalm-return array<array{scalar|list<string>|null, bool}>
     */
    public static function integerOnlyProvider(): array
    {
        return [
            [false, true],
            [true, true],
            [0, false],
            [1, true],
            [0.0, true],
            [1.0, true],
            ['', true],
            ['abc', true],
            ['0', true],
            ['1', true],
            [[], true],
            [['xxx'], true],
            [null, true],
        ];
    }

    #[DataProvider('floatOnlyProvider')]
    public function testOnlyFloat(mixed $value, bool $valid): void
    {
        $validator = new NotEmpty(['type' => NotEmpty::FLOAT]);
        self::assertSame($valid, $validator->isValid($value));
    }

    /**
     * Provides values and their expected validity for boolean empty
     *
     * @psalm-return array<array{scalar|list<string>|null, bool}>
     */
    public static function floatOnlyProvider(): array
    {
        return [
            [false, true],
            [true, true],
            [0, true],
            [1, true],
            [0.0, false],
            [1.0, true],
            ['', true],
            ['abc', true],
            ['0', true],
            ['1', true],
            [[], true],
            [['xxx'], true],
            [null, true],
        ];
    }

    /**
     * Ensures that the validator follows expected behavior
     */
    #[DataProvider('stringOnlyProvider')]
    public function testOnlyString(mixed $value, bool $valid): void
    {
        $validator = new NotEmpty(['type' => NotEmpty::STRING]);
        self::assertSame($valid, $validator->isValid($value));
    }

    /**
     * Provides values and their expected validity for boolean empty
     *
     * @psalm-return array<array{scalar|list<string>|null, bool}>
     */
    public static function stringOnlyProvider(): array
    {
        return [
            [false, true],
            [true, true],
            [0, true],
            [1, true],
            [0.0, true],
            [1.0, true],
            ['', false],
            ['abc', true],
            ['0', true],
            ['1', true],
            [[], true],
            [['xxx'], true],
            [null, true],
        ];
    }

    #[DataProvider('zeroOnlyProvider')]
    public function testOnlyZero(mixed $value, bool $valid): void
    {
        $validator = new NotEmpty(['type' => NotEmpty::ZERO]);
        self::assertSame($valid, $validator->isValid($value));
    }

    /**
     * Provides values and their expected validity for boolean empty
     *
     * @psalm-return array<array{scalar|list<string>|null, bool}>
     */
    public static function zeroOnlyProvider(): array
    {
        return [
            [false, true],
            [true, true],
            [0, true],
            [1, true],
            [0.0, true],
            [1.0, true],
            ['', true],
            ['abc', true],
            ['0', false],
            ['1', true],
            [[], true],
            [['xxx'], true],
            [null, true],
        ];
    }

    #[DataProvider('arrayOnlyProvider')]
    public function testOnlyArray(mixed $value, bool $valid): void
    {
        $validator = new NotEmpty(['type' => NotEmpty::EMPTY_ARRAY]);
        self::assertSame($valid, $validator->isValid($value));
    }

    /**
     * Provides values and their expected validity for boolean empty
     *
     * @psalm-return array<array{scalar|list<string>|null, bool}>
     */
    public static function arrayOnlyProvider(): array
    {
        return [
            [false, true],
            [true, true],
            [0, true],
            [1, true],
            [0.0, true],
            [1.0, true],
            ['', true],
            ['abc', true],
            ['0', true],
            ['1', true],
            [[], false],
            [['xxx'], true],
            [null, true],
        ];
    }

    #[DataProvider('nullOnlyProvider')]
    public function testOnlyNull(mixed $value, bool $valid): void
    {
        $validator = new NotEmpty(['type' => NotEmpty::NULL]);
        self::assertSame($valid, $validator->isValid($value));
    }

    /**
     * Provides values and their expected validity for boolean empty
     *
     * @psalm-return array<array{scalar|list<string>|null, bool}>
     */
    public static function nullOnlyProvider(): array
    {
        return [
            [false, true],
            [true, true],
            [0, true],
            [1, true],
            [0.0, true],
            [1.0, true],
            ['', true],
            ['abc', true],
            ['0', true],
            ['1', true],
            [[], true],
            [['xxx'], true],
            [null, false],
        ];
    }

    #[DataProvider('phpOnlyProvider')]
    public function testOnlyPHP(mixed $value, bool $valid): void
    {
        $validator = new NotEmpty(['type' => NotEmpty::PHP]);
        self::assertSame($valid, $validator->isValid($value));
    }

    /**
     * Provides values and their expected validity for boolean empty
     *
     * @psalm-return array<array{scalar|list<string>|null, bool}>
     */
    public static function phpOnlyProvider(): array
    {
        return [
            [false, false],
            [true, true],
            [0, false],
            [1, true],
            [0.0, false],
            [1.0, true],
            ['', false],
            ['abc', true],
            ['0', false],
            ['1', true],
            [[], false],
            [['xxx'], true],
            [null, false],
        ];
    }

    #[DataProvider('spaceOnlyProvider')]
    public function testOnlySpace(mixed $value, bool $valid): void
    {
        $validator = new NotEmpty(['type' => NotEmpty::SPACE]);
        self::assertSame($valid, $validator->isValid($value));
    }

    /**
     * Provides values and their expected validity for boolean empty
     *
     * @psalm-return array<array{scalar|list<string>|null, bool}>
     */
    public static function spaceOnlyProvider(): array
    {
        return [
            [false, true],
            [true, true],
            [0, true],
            [1, true],
            [0.0, true],
            [1.0, true],
            ['', true],
            ['abc', true],
            ['0', true],
            ['1', true],
            [[], true],
            [['xxx'], true],
            [null, true],
        ];
    }

    #[DataProvider('onlyAllProvider')]
    public function testOnlyAll(mixed $value, bool $valid): void
    {
        $validator = new NotEmpty(['type' => NotEmpty::ALL]);
        self::assertSame($valid, $validator->isValid($value));
    }

    /**
     * Provides values and their expected validity for boolean empty
     *
     * @psalm-return array<array{scalar|list<string>|null, bool}>
     */
    public static function onlyAllProvider(): array
    {
        return [
            [false, false],
            [true, true],
            [0, false],
            [1, true],
            [0.0, false],
            [1.0, true],
            ['', false],
            ['abc', true],
            ['0', false],
            ['1', true],
            [[], false],
            [['xxx'], true],
            [null, false],
        ];
    }

    #[DataProvider('arrayConstantNotationProvider')]
    public function testArrayConstantNotation(mixed $value, bool $valid): void
    {
        $validator = new NotEmpty([
            'type' => [
                NotEmpty::ZERO,
                NotEmpty::STRING,
                NotEmpty::BOOLEAN,
            ],
        ]);

        self::assertSame($valid, $validator->isValid($value));
    }

    /**
     * Provides values and their expected validity for boolean empty
     *
     * @psalm-return array<array{scalar|list<string>|null, bool}>
     */
    public static function arrayConstantNotationProvider(): array
    {
        return [
            [false, false],
            [true, true],
            [0, true],
            [1, true],
            [0.0, true],
            [1.0, true],
            ['', false],
            ['abc', true],
            ['0', false],
            ['1', true],
            [[], true],
            [['xxx'], true],
            [null, true],
        ];
    }

    #[DataProvider('multiConstantNotationProvider')]
    public function testMultiConstantNotation(mixed $value, bool $valid): void
    {
        $validator = new NotEmpty([
            'type' => NotEmpty::ZERO | NotEmpty::STRING | NotEmpty::BOOLEAN,
        ]);

        self::assertSame($valid, $validator->isValid($value));
    }

    /**
     * Provides values and their expected validity for boolean empty
     *
     * @psalm-return array<array{scalar|list<string>|null, bool}>
     */
    public static function multiConstantNotationProvider(): array
    {
        return [
            [false, false],
            [true, true],
            [0, true],
            [1, true],
            [0.0, true],
            [1.0, true],
            ['', false],
            ['abc', true],
            ['0', false],
            ['1', true],
            [[], true],
            [['xxx'], true],
            [null, true],
        ];
    }

    #[DataProvider('stringNotationProvider')]
    public function testStringNotation(mixed $value, bool $valid): void
    {
        $validator = new NotEmpty([
            'type' => ['zero', 'string', 'boolean'],
        ]);

        self::assertSame($valid, $validator->isValid($value));
    }

    /**
     * Provides values and their expected validity for boolean empty
     *
     * @psalm-return array<array{scalar|list<string>|null, bool}>
     */
    public static function stringNotationProvider(): array
    {
        return [
            [false, false],
            [true, true],
            [0, true],
            [1, true],
            [0.0, true],
            [1.0, true],
            ['', false],
            ['abc', true],
            ['0', false],
            ['1', true],
            [[], true],
            [['xxx'], true],
            [null, true],
        ];
    }

    /**
     * Ensures that the validator follows expected behavior so if a string is specified more than once, it doesn't
     * cause different validations to run
     */
    #[DataProvider('duplicateStringSettingProvider')]
    public function testStringNotationWithDuplicate(string $string, int $expected): void
    {
        $validator = new NotEmpty([
            'type' => [$string, $string],
        ]);

        $prop = new ReflectionProperty($validator, 'type');

        self::assertSame($expected, $prop->getValue($validator));
    }

    /**
     * Data provider for testStringNotationWithDuplicate method. Provides a string which will be duplicated. The test
     * ensures that setting a string value more than once only turns on the appropriate bit once
     *
     * @psalm-return array<array{string, int}>
     */
    public static function duplicateStringSettingProvider(): array
    {
        return [
            ['boolean',      NotEmpty::BOOLEAN],
            ['integer',      NotEmpty::INTEGER],
            ['float',        NotEmpty::FLOAT],
            ['string',       NotEmpty::STRING],
            ['zero',         NotEmpty::ZERO],
            ['array',        NotEmpty::EMPTY_ARRAY],
            ['null',         NotEmpty::NULL],
            ['php',          NotEmpty::PHP],
            ['space',        NotEmpty::SPACE],
            ['object',       NotEmpty::OBJECT],
            ['objectstring', NotEmpty::OBJECT_STRING],
            ['objectcount',  NotEmpty::OBJECT_COUNT],
            ['all',          NotEmpty::ALL],
        ];
    }

    #[DataProvider('singleStringNotationProvider')]
    public function testSingleStringConstructorNotation(mixed $value, bool $valid): void
    {
        $validator = new NotEmpty([
            'type' => 'boolean',
        ]);

        self::assertSame($valid, $validator->isValid($value));
    }

    /**
     * Provides values and their expected validity for boolean empty
     *
     * @psalm-return array<array{scalar|list<string>|null, bool}>
     */
    public static function singleStringNotationProvider(): array
    {
        return [
            [false, false],
            [true, true],
            [0, true],
            [1, true],
            [0.0, true],
            [1.0, true],
            ['', true],
            ['abc', true],
            ['0', true],
            ['1', true],
            [[], true],
            [['xxx'], true],
            [null, true],
        ];
    }

    #[Group('Laminas-3236')]
    public function testStringWithZeroShouldNotBeTreatedAsEmpty(): void
    {
        self::assertTrue($this->validator->isValid('0'));
    }

    /**
     * Ensures that getMessages() returns expected default value
     */
    public function testGetMessages(): void
    {
        self::assertSame([], $this->validator->getMessages());
    }

    /**
     * @Laminas-4352
     */
    public function testNonStringValidation(): void
    {
        $v2 = new NotEmpty();

        self::assertTrue($this->validator->isValid($v2));
    }

    /**
     * @Laminas-8767
     */
    public function testLaminas8767(): void
    {
        $valid = new NotEmpty(['type' => NotEmpty::STRING]);

        self::assertFalse($valid->isValid(''));

        $messages = $valid->getMessages();

        self::assertArrayHasKey('isEmpty', $messages);
        self::assertStringContainsString("can't be empty", $messages['isEmpty']);
    }

    public function testObjects(): void
    {
        $valid  = new NotEmpty(['type' => NotEmpty::STRING]);
        $object = new stdClass();

        self::assertFalse($valid->isValid($object));

        $valid = new NotEmpty(['type' => NotEmpty::OBJECT]);

        self::assertTrue($valid->isValid($object));
    }

    public function testStringObjects(): void
    {
        $valid = new NotEmpty(['type' => NotEmpty::STRING]);

        $object = new class () {
            public function __toString(): string
            {
                return 'Test';
            }
        };
        self::assertFalse($valid->isValid($object));

        $valid = new NotEmpty(['type' => NotEmpty::OBJECT_STRING]);
        self::assertTrue($valid->isValid($object));

        $object = new class () {
            public function __toString(): string
            {
                return '';
            }
        };

        self::assertFalse($valid->isValid($object));
    }

    #[DataProvider('arrayConfigNotationWithoutKeyProvider')]
    #[Group('Laminas-11566')]
    public function testArrayConfigNotationWithoutKey(mixed $value, bool $valid): void
    {
        $validator = new NotEmpty([
            'type' => ['zero', 'string', 'boolean'],
        ]);

        self::assertSame($valid, $validator->isValid($value));
    }

    /**
     * Provides values and their expected validity for boolean empty
     *
     * @psalm-return array<array{scalar|list<string>|null, bool}>
     */
    public static function arrayConfigNotationWithoutKeyProvider(): array
    {
        return [
            [false, false],
            [true, true],
            [0, true],
            [1, true],
            [0.0, true],
            [1.0, true],
            ['', false],
            ['abc', true],
            ['0', false],
            ['1', true],
            [[], true],
            [['xxx'], true],
            [null, true],
        ];
    }
}
