<?php

declare(strict_types=1);

namespace LaminasTest\Validator;

use ArrayObject;
use Laminas\Validator\Exception\InvalidArgumentException;
use Laminas\Validator\NotEmpty;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use stdClass;

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
     * @param array $types Array of type strings or constants
     * @param integer $expected Expected value of calculated type
     */
    #[DataProvider('constructorWithTypeArrayProvider')]
    public function testConstructorWithTypeArray(array $types, int $expected): void
    {
        $validator = new NotEmpty($types);

        self::assertSame($expected, $validator->getType());
    }

    /**
     * @psalm-return array<array-key, array{list<string|int>, int}>
     */
    public static function constructorWithTypeArrayProvider(): array
    {
        return [
            [['php', 'boolean'], NotEmpty::PHP],
            [['boolean', 'boolean'], NotEmpty::BOOLEAN],
            [[NotEmpty::PHP, NotEmpty::BOOLEAN], NotEmpty::PHP],
            [[NotEmpty::BOOLEAN, NotEmpty::BOOLEAN], NotEmpty::BOOLEAN],
        ];
    }

    /**
     * Ensures that the validator follows expected behavior
     *
     * Laminas-6708 introduces a change for validating integer 0; it is a valid
     * integer value. '0' is also valid.
     *
     * @param mixed $value Value to test
     * @param boolean $valid Expected validity of value
     */
    #[DataProvider('basicProvider')]
    #[Group('Laminas-6708')]
    public function testBasic(mixed $value, bool $valid): void
    {
        $this->checkValidationValue($value, $valid);
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

    /**
     * Ensures that the validator follows expected behavior
     *
     * @param mixed $value Value to test
     * @param boolean $valid Expected validity of value
     */
    #[DataProvider('booleanProvider')]
    public function testOnlyBoolean(mixed $value, bool $valid): void
    {
        $this->validator->setType(NotEmpty::BOOLEAN);

        $this->checkValidationValue($value, $valid);
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

    /**
     * Ensures that the validator follows expected behavior
     *
     * @param mixed $value Value to test
     * @param boolean $valid Expected validity of value
     */
    #[DataProvider('integerOnlyProvider')]
    public function testOnlyInteger(mixed $value, bool $valid): void
    {
        $this->validator->setType(NotEmpty::INTEGER);

        $this->checkValidationValue($value, $valid);
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

    /**
     * Ensures that the validator follows expected behavior
     *
     * @param mixed $value Value to test
     * @param boolean $valid Expected validity of value
     */
    #[DataProvider('floatOnlyProvider')]
    public function testOnlyFloat(mixed $value, bool $valid): void
    {
        $this->validator->setType(NotEmpty::FLOAT);

        $this->checkValidationValue($value, $valid);
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
     *
     * @param mixed $value Value to test
     * @param boolean $valid Expected validity of value
     */
    #[DataProvider('stringOnlyProvider')]
    public function testOnlyString(mixed $value, bool $valid): void
    {
        $this->validator->setType(NotEmpty::STRING);

        $this->checkValidationValue($value, $valid);
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

    /**
     * Ensures that the validator follows expected behavior
     *
     * @param mixed $value Value to test
     * @param boolean $valid Expected validity of value
     */
    #[DataProvider('zeroOnlyProvider')]
    public function testOnlyZero(mixed $value, bool $valid): void
    {
        $this->validator->setType(NotEmpty::ZERO);

        $this->checkValidationValue($value, $valid);
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

    /**
     * Ensures that the validator follows expected behavior
     *
     * @param mixed $value Value to test
     * @param boolean $valid Expected validity of value
     */
    #[DataProvider('arrayOnlyProvider')]
    public function testOnlyArray(mixed $value, bool $valid): void
    {
        $this->validator->setType(NotEmpty::EMPTY_ARRAY);

        $this->checkValidationValue($value, $valid);
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

    /**
     * Ensures that the validator follows expected behavior
     *
     * @param mixed $value Value to test
     * @param boolean $valid Expected validity of value
     */
    #[DataProvider('nullOnlyProvider')]
    public function testOnlyNull(mixed $value, bool $valid): void
    {
        $this->validator->setType(NotEmpty::NULL);

        $this->checkValidationValue($value, $valid);
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

    /**
     * Ensures that the validator follows expected behavior
     *
     * @param mixed $value Value to test
     * @param boolean $valid Expected validity of value
     */
    #[DataProvider('phpOnlyProvider')]
    public function testOnlyPHP(mixed $value, bool $valid): void
    {
        $this->validator->setType(NotEmpty::PHP);

        $this->checkValidationValue($value, $valid);
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

    /**
     * Ensures that the validator follows expected behavior
     *
     * @param mixed $value Value to test
     * @param boolean $valid Expected validity of value
     */
    #[DataProvider('spaceOnlyProvider')]
    public function testOnlySpace(mixed $value, bool $valid): void
    {
        $this->validator->setType(NotEmpty::SPACE);

        $this->checkValidationValue($value, $valid);
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

    /**
     * Ensures that the validator follows expected behavior
     *
     * @param mixed $value Value to test
     * @param boolean $valid Expected validity of value
     */
    #[DataProvider('onlyAllProvider')]
    public function testOnlyAll(mixed $value, bool $valid): void
    {
        $this->validator->setType(NotEmpty::ALL);

        $this->checkValidationValue($value, $valid);
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

    /**
     * Ensures that the validator follows expected behavior
     *
     * @param mixed $value Value to test
     * @param boolean $valid Expected validity of value
     */
    #[DataProvider('arrayConstantNotationProvider')]
    public function testArrayConstantNotation(mixed $value, bool $valid): void
    {
        $this->validator = new NotEmpty([
            'type' => [
                NotEmpty::ZERO,
                NotEmpty::STRING,
                NotEmpty::BOOLEAN,
            ],
        ]);

        $this->checkValidationValue($value, $valid);
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

    /**
     * Ensures that the validator follows expected behavior
     *
     * @param mixed $value Value to test
     * @param boolean $valid Expected validity of value
     */
    #[DataProvider('arrayConfigNotationProvider')]
    public function testArrayConfigNotation(mixed $value, bool $valid): void
    {
        $this->validator = new NotEmpty([
            'type' => [
                NotEmpty::ZERO,
                NotEmpty::STRING,
                NotEmpty::BOOLEAN,
            ],
            'test' => false,
        ]);

        $this->checkValidationValue($value, $valid);
    }

    /**
     * Provides values and their expected validity for boolean empty
     *
     * @psalm-return array<array{scalar|list<string>|null, bool}>
     */
    public static function arrayConfigNotationProvider(): array
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
     * Ensures that the validator follows expected behavior
     *
     * @param mixed $value Value to test
     * @param boolean $valid Expected validity of value
     */
    #[DataProvider('multiConstantNotationProvider')]
    public function testMultiConstantNotation(mixed $value, bool $valid): void
    {
        $this->validator = new NotEmpty(
            NotEmpty::ZERO + NotEmpty::STRING + NotEmpty::BOOLEAN
        );

        $this->checkValidationValue($value, $valid);
    }

    /**
     * Ensures that the validator follows expected behavior
     *
     * @param mixed $value Value to test
     * @param boolean $valid Expected validity of value
     */
    #[DataProvider('multiConstantNotationProvider')]
    public function testMultiConstantBooleanOrNotation(mixed $value, bool $valid): void
    {
        $this->validator = new NotEmpty(
            NotEmpty::ZERO | NotEmpty::STRING | NotEmpty::BOOLEAN
        );

        $this->checkValidationValue($value, $valid);
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

    /**
     * Ensures that the validator follows expected behavior
     *
     * @param mixed $value Value to test
     * @param boolean $valid Expected validity of value
     */
    #[DataProvider('stringNotationProvider')]
    public function testStringNotation(mixed $value, bool $valid): void
    {
        $this->validator = new NotEmpty([
            'type' => ['zero', 'string', 'boolean'],
        ]);

        $this->checkValidationValue($value, $valid);
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
     *
     * @param string  $string   Array of string type values
     * @param integer $expected Expected type setting value
     */
    #[DataProvider('duplicateStringSettingProvider')]
    public function testStringNotationWithDuplicate(string $string, int $expected): void
    {
        $type = [$string, $string];
        $this->validator->setType($type);

        self::assertSame($expected, $this->validator->getType());
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

    /**
     * Ensures that the validator follows expected behavior
     *
     * @param mixed $value Value to test
     * @param boolean $valid Expected validity of value
     */
    #[DataProvider('singleStringNotationProvider')]
    public function testSingleStringConstructorNotation(mixed $value, bool $valid): void
    {
        $this->validator = new NotEmpty(
            'boolean'
        );

        $this->checkValidationValue($value, $valid);
    }

    /**
     * Ensures that the validator follows expected behavior
     *
     * @param mixed $value Value to test
     * @param boolean $valid Expected validity of value
     */
    #[DataProvider('singleStringNotationProvider')]
    public function testSingleStringSetTypeNotation(mixed $value, bool $valid): void
    {
        $this->validator->setType('boolean');

        $this->checkValidationValue($value, $valid);
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

    /**
     * Ensures that the validator follows expected behavior
     *
     * @param mixed $value Value to test
     * @param boolean $valid Expected validity of value
     */
    #[DataProvider('configObjectProvider')]
    public function testTraversableObject(mixed $value, bool $valid): void
    {
        $options = ['type' => 'all'];
        $config  = new ArrayObject($options);

        $this->validator = new NotEmpty(
            $config
        );

        $this->checkValidationValue($value, $valid);
    }

    /**
     * Provides values and their expected validity for boolean empty
     *
     * @psalm-return array<array{scalar|list<string>|null, bool}>
     */
    public static function configObjectProvider(): array
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

    /**
     * Ensures that the validator follows expected behavior
     */
    public function testSettingFalseType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown');

        $this->validator->setType(true);
    }

    /**
     * Ensures that the validator follows expected behavior
     */
    public function testGetType(): void
    {
        self::assertSame($this->validator->getDefaultType(), $this->validator->getType());
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
        $valid = new NotEmpty(NotEmpty::STRING);

        self::assertFalse($valid->isValid(''));

        $messages = $valid->getMessages();

        self::assertArrayHasKey('isEmpty', $messages);
        self::assertStringContainsString("can't be empty", $messages['isEmpty']);
    }

    public function testObjects(): void
    {
        $valid  = new NotEmpty(NotEmpty::STRING);
        $object = new stdClass();

        self::assertFalse($valid->isValid($object));

        $valid = new NotEmpty(NotEmpty::OBJECT);

        self::assertTrue($valid->isValid($object));
    }

    public function testStringObjects(): void
    {
        $valid = new NotEmpty(NotEmpty::STRING);

        $object = new class () {
            public function __toString(): string
            {
                return 'Test';
            }
        };
        self::assertFalse($valid->isValid($object));

        $valid = new NotEmpty(NotEmpty::OBJECT_STRING);
        self::assertTrue($valid->isValid($object));

        $object = new class () {
            public function __toString(): string
            {
                return '';
            }
        };

        self::assertFalse($valid->isValid($object));
    }

    /**
     * @param mixed $value Value to test
     * @param boolean $valid Expected validity of value
     */
    #[DataProvider('arrayConfigNotationWithoutKeyProvider')]
    #[Group('Laminas-11566')]
    public function testArrayConfigNotationWithoutKey(mixed $value, bool $valid): void
    {
        $this->validator = new NotEmpty(
            ['zero', 'string', 'boolean']
        );

        $this->checkValidationValue($value, $valid);
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

    public function testEqualsMessageTemplates(): void
    {
        $messageTemplates = [
            'isEmpty'         => "Value is required and can't be empty",
            'notEmptyInvalid' => "Invalid type given. String, integer, float, boolean or array expected",
        ];

        self::assertSame($messageTemplates, $this->validator->getOption('messageTemplates'));
    }

    public function testTypeAutoDetectionHasNoSideEffect(): void
    {
        $validator = new NotEmpty(['translatorEnabled' => true]);

        self::assertSame($validator->getDefaultType(), $validator->getType());
    }

    public function testDefaultType(): void
    {
        self::assertSame(
            NotEmpty::BOOLEAN
                | NotEmpty::STRING
                | NotEmpty::EMPTY_ARRAY
                | NotEmpty::NULL
                | NotEmpty::SPACE
                | NotEmpty::OBJECT,
            $this->validator->getDefaultType()
        );
    }

    /**
     * Checks that the validation value matches the expected validity
     *
     * @param mixed $value Value to validate
     * @param bool  $valid Expected validity
     */
    protected function checkValidationValue(mixed $value, bool $valid): void
    {
        $isValid = $this->validator->isValid($value);

        if ($valid) {
            self::assertTrue($isValid);
        } else {
            self::assertFalse($isValid);
        }
    }
}
