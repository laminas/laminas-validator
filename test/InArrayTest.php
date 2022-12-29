<?php

declare(strict_types=1);

namespace LaminasTest\Validator;

use Laminas\Validator\Exception\RuntimeException;
use Laminas\Validator\InArray;
use LaminasTest\Validator\TestAsset\Enum\TestBackedIntEnum;
use LaminasTest\Validator\TestAsset\Enum\TestBackedStringEnum;
use LaminasTest\Validator\TestAsset\Enum\TestUnitEnum;
use PHPUnit\Framework\TestCase;

use function array_keys;

use const PHP_MAJOR_VERSION;

/**
 * @group Laminas_Validator
 * @covers \Laminas\Validator\InArray
 */
final class InArrayTest extends TestCase
{
    private InArray $validator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = new InArray(
            [
                'haystack' => [1, 2, 3],
            ]
        );
    }

    /**
     * @return array<string,array{0:list<mixed>,1:mixed,2:mixed}>
     */
    public function nonStrictValidationSet(): array
    {
        return [
            'strings'             => [
                ['Y', 'N'],
                'Y',
                'X',
            ],
            'integers'            => [
                [1, 2],
                1,
                3,
            ],
            'integerish haystack' => [
                ['1', '2'],
                1,
                3,
            ],
            'integerish values'   => [
                [1, 2],
                '1',
                '3',
            ],
        ];
    }

    /**
     * @return array<string,array{0:list<mixed>,1:mixed,2:mixed}>
     */
    public function strictValidationSet(): array
    {
        return [
            'strings'             => [
                ['Y', 'N'],
                'Y',
                'X',
            ],
            'integers'            => [
                [1, 2],
                1,
                3,
            ],
            'integerish haystack' => [
                ['1', '2'],
                '1',
                1,
            ],
            'integerish values'   => [
                [1, 2],
                1,
                '1',
            ],
        ];
    }

    /**
     * Ensures that getMessages() returns expected default value
     */
    public function testGetMessages(): void
    {
        self::assertSame([], $this->validator->getMessages());
    }

    /**
     * Ensures that getHaystack() returns expected value
     */
    public function testGetHaystack(): void
    {
        self::assertSame([1, 2, 3], $this->validator->getHaystack());
    }

    public function testUnsetHaystackRaisesException(): void
    {
        $validator = new InArray();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('haystack option is mandatory');

        $validator->getHaystack();
    }

    /**
     * Ensures that getStrict() returns expected default value
     */
    public function testGetStrict(): void
    {
        self::assertFalse($this->validator->getStrict());
    }

    public function testGivingOptionsAsArrayAtInitiation(): void
    {
        $validator = new InArray(
            [
                'haystack' => [1, 'a', 2.3],
            ]
        );

        self::assertTrue($validator->isValid(1));
        self::assertTrue($validator->isValid(1.0));
        self::assertTrue($validator->isValid('1'));
        self::assertTrue($validator->isValid('a'));
        self::assertFalse($validator->isValid('A'));
        self::assertTrue($validator->isValid(2.3));
        self::assertTrue($validator->isValid(2.3e0));
    }

    public function testSettingANewHaystack(): void
    {
        $this->validator->setHaystack([1, 'a', 2.3]);

        self::assertSame([1, 'a', 2.3], $this->validator->getHaystack());
    }

    /**
     * @group Laminas-337
     */
    public function testSettingNewStrictMode(): void
    {
        $validator = new InArray(
            [
                'haystack' => ['test', 0, 'A', 0.0],
            ]
        );

        // test non-strict with vulnerability prevention (default choice)
        $validator->setStrict(InArray::COMPARE_NOT_STRICT_AND_PREVENT_STR_TO_INT_VULNERABILITY);
        self::assertFalse($validator->getStrict());

        $validator->setStrict(InArray::COMPARE_STRICT);
        self::assertTrue($validator->getStrict());

        $validator->setStrict(InArray::COMPARE_NOT_STRICT);
        self::assertSame(InArray::COMPARE_NOT_STRICT, $validator->getStrict());
    }

    public function testNonStrictSafeComparisons(): void
    {
        $validator = new InArray(
            [
                'haystack' => ['test', 0, 'A', 1, 0.0],
            ]
        );

        self::assertFalse($validator->getStrict());
        self::assertFalse($validator->isValid('b'));
        self::assertFalse($validator->isValid('a'));
        self::assertTrue($validator->isValid('A'));
        self::assertTrue($validator->isValid('0'));
        self::assertFalse($validator->isValid('1a'));
        self::assertTrue($validator->isValid(0));
    }

    public function testStrictComparisons(): void
    {
        $validator = new InArray(
            [
                'haystack' => ['test', 0, 'A', 1, 0.0],
            ]
        );

        // bog standard strict compare
        $validator->setStrict(InArray::COMPARE_STRICT);

        self::assertTrue($validator->getStrict());

        self::assertTrue($validator->isValid('A'));
        self::assertTrue($validator->isValid(0));
        self::assertFalse($validator->isValid('b'));
        self::assertFalse($validator->isValid('a'));
        self::assertFalse($validator->isValid('0'));
        self::assertFalse($validator->isValid('1a'));
    }

    public function testNonStrictComparisons(): void
    {
        $validator = new InArray(
            [
                'haystack' => ['test', 0, 'A', 1, 0.0],
            ]
        );

        // non-numeric strings converted to 0
        $validator->setStrict(InArray::COMPARE_NOT_STRICT);

        self::assertSame(InArray::COMPARE_NOT_STRICT, $validator->getStrict());
        self::assertTrue($validator->isValid('b'));
        self::assertTrue($validator->isValid('a'));
        self::assertTrue($validator->isValid('A'));
        self::assertTrue($validator->isValid('0'));
        self::assertTrue($validator->isValid('1a'));
        self::assertTrue($validator->isValid(0));
    }

    public function testNonStrictSafeComparisonsRecurisve(): void
    {
        $validator = new InArray(
            [
                'haystack' => [
                    ['test', 0, 'A', 0.0],
                    ['foo', 1, 'a', 'c'],
                ],
            ]
        );

        $validator->setRecursive(true);

        self::assertFalse($validator->getStrict());
        self::assertFalse($validator->isValid('b'));
        self::assertTrue($validator->isValid('a'));
        self::assertTrue($validator->isValid('A'));
        self::assertTrue($validator->isValid('0'));
        self::assertFalse($validator->isValid('1a'));
        self::assertTrue($validator->isValid(0));
    }

    public function testStrictComparisonsRecursive(): void
    {
        $validator = new InArray(
            [
                'haystack' => [
                    ['test', 0, 'A', 0.0],
                    ['foo', 1, 'a', 'c'],
                ],
            ]
        );

        // bog standard strict compare
        $validator->setStrict(InArray::COMPARE_STRICT);
        $validator->setRecursive(true);

        self::assertTrue($validator->getStrict());
        self::assertFalse($validator->isValid('b'));
        self::assertTrue($validator->isValid('a'));
        self::assertTrue($validator->isValid('A'));
        self::assertFalse($validator->isValid('0'));
        self::assertFalse($validator->isValid('1a'));
        self::assertTrue($validator->isValid(0));
    }

    public function testNonStrictComparisonsRecursive(): void
    {
        $validator = new InArray(
            [
                'haystack' => [
                    ['test', 0, 'A', 0.0],
                    ['foo', 1, 'a', 'c'],
                ],
            ]
        );

        // non-numeric strings converted to 0
        $validator->setStrict(InArray::COMPARE_NOT_STRICT);
        $validator->setRecursive(true);

        $stringToNumericComparisonAssertion = PHP_MAJOR_VERSION < 8 ? 'assertTrue' : 'assertFalse';

        self::assertSame(InArray::COMPARE_NOT_STRICT, $validator->getStrict());

        $this->$stringToNumericComparisonAssertion($validator->isValid('b'));

        self::assertTrue($validator->isValid('a'));
        self::assertTrue($validator->isValid('A'));
        self::assertTrue($validator->isValid('0'));

        $this->$stringToNumericComparisonAssertion($validator->isValid('1a'));

        self::assertTrue($validator->isValid(0));
    }

    public function testIntegerInputAndStringInHaystack(): void
    {
        $validator = new InArray(
            [
                'haystack' => ['test', 1, 2],
            ]
        );

        $validator->setStrict(InArray::COMPARE_NOT_STRICT_AND_PREVENT_STR_TO_INT_VULNERABILITY);
        self::assertFalse($validator->isValid(0));

        $validator->setStrict(InArray::COMPARE_NOT_STRICT);
        self::assertTrue($validator->isValid(0));

        $validator->setStrict(InArray::COMPARE_STRICT);
        self::assertFalse($validator->isValid(0));
    }

    public function testFloatInputAndStringInHaystack(): void
    {
        $validator = new InArray(
            [
                'haystack' => ['test', 1, 2],
            ]
        );

        $validator->setStrict(InArray::COMPARE_NOT_STRICT_AND_PREVENT_STR_TO_INT_VULNERABILITY);
        self::assertFalse($validator->isValid(0.0));

        $validator->setStrict(InArray::COMPARE_NOT_STRICT);
        self::assertTrue($validator->isValid(0.0));

        $validator->setStrict(InArray::COMPARE_STRICT);
        self::assertFalse($validator->isValid(0.0));
    }

    public function testNumberStringInputAgainstNumberInHaystack(): void
    {
        $validator = new InArray(
            [
                'haystack' => [1, 2],
            ]
        );

        $validator->setStrict(InArray::COMPARE_NOT_STRICT_AND_PREVENT_STR_TO_INT_VULNERABILITY);
        self::assertFalse($validator->isValid('1asdf'));

        $validator->setStrict(InArray::COMPARE_NOT_STRICT);
        self::assertTrue($validator->isValid('1asdf'));

        $validator->setStrict(InArray::COMPARE_STRICT);
        self::assertFalse($validator->isValid('1asdf'));
    }

    public function testFloatStringInputAgainstNumberInHaystack(): void
    {
        $validator = new InArray(
            [
                'haystack' => [1.5, 2.4],
            ]
        );

        $validator->setStrict(InArray::COMPARE_NOT_STRICT_AND_PREVENT_STR_TO_INT_VULNERABILITY);
        self::assertFalse($validator->isValid('1.5asdf'));

        $validator->setStrict(InArray::COMPARE_NOT_STRICT);
        self::assertTrue($validator->isValid('1.5asdf'));

        $validator->setStrict(InArray::COMPARE_STRICT);
        self::assertFalse($validator->isValid('1.5asdf'));
    }

    public function testSettingStrictViaInitiation(): void
    {
        $validator = new InArray(
            [
                'haystack' => ['test', 0, 'A'],
                'strict'   => true,
            ]
        );

        self::assertTrue($validator->getStrict());
    }

    public function testGettingRecursiveOption(): void
    {
        self::assertFalse($this->validator->getRecursive());

        $this->validator->setRecursive(true);

        self::assertTrue($this->validator->getRecursive());
    }

    public function testSettingRecursiveViaInitiation(): void
    {
        $validator = new InArray(
            [
                'haystack'  => ['test', 0, 'A'],
                'recursive' => true,
            ]
        );

        self::assertTrue($validator->getRecursive());
    }

    public function testRecursiveDetection(): void
    {
        $validator = new InArray(
            [
                'haystack'
                 => [
                     'firstDimension'  => ['test', 0, 'A'],
                     'secondDimension' => ['value', 2, 'a'],
                 ],
                'recursive' => false,
            ]
        );

        self::assertFalse($validator->isValid('A'));

        $validator->setRecursive(true);

        self::assertTrue($validator->isValid('A'));
    }

    public function testEqualsMessageTemplates(): void
    {
        self::assertSame(
            [
                InArray::NOT_IN_ARRAY,
            ],
            array_keys($this->validator->getMessageTemplates())
        );
        self::assertSame($this->validator->getOption('messageTemplates'), $this->validator->getMessageTemplates());
    }

    /**
     * @link https://github.com/laminas/laminas-validator/issues/81
     *
     * @param list<mixed> $haystack
     * @dataProvider strictValidationSet
     */
    public function testBooleanStrictEnforcesStrictMode(array $haystack, mixed $valid, mixed $invalid): void
    {
        $validator = new InArray([
            'haystack' => $haystack,
            'strict'   => true,
        ]);

        self::assertTrue($validator->isValid($valid));
        self::assertFalse($validator->isValid($invalid));
    }

    /**
     * @link https://github.com/laminas/laminas-validator/issues/81
     *
     * @param list<mixed> $haystack
     * @dataProvider nonStrictValidationSet
     */
    public function testBooleanNotStrictEnforcesNonStrictMode(array $haystack, mixed $valid, mixed $invalid): void
    {
        $validator = new InArray([
            'haystack' => $haystack,
            'strict'   => false,
        ]);

        self::assertTrue($validator->isValid($valid));
        self::assertFalse($validator->isValid($invalid));
    }

    /**
     * @requires PHP 8.1
     */
    public function testEnumValidation(): void
    {
        $validator = new InArray([
            'haystack' => TestUnitEnum::class,
        ]);

        self::assertTrue($validator->isValid('foo'));
        self::assertFalse($validator->isValid('baz'));

        $validator = new InArray([
            'haystack' => TestBackedStringEnum::class,
        ]);

        self::assertTrue($validator->isValid('foo'));
        self::assertFalse($validator->isValid('baz'));

        $validator = new InArray([
            'haystack' => TestBackedIntEnum::class,
        ]);

        self::assertTrue($validator->isValid(1));
        self::assertFalse($validator->isValid(3));

        $validator = new InArray([
            'haystack' => TestBackedIntEnum::class,
            'strict'   => true,
        ]);

        self::assertTrue($validator->isValid(1));
        self::assertFalse($validator->isValid('2'));
    }
}
