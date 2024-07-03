<?php

declare(strict_types=1);

namespace LaminasTest\Validator;

use Laminas\Validator\Exception\InvalidArgumentException;
use Laminas\Validator\InArray;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use function array_keys;

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
    public static function nonStrictValidationSet(): array
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
    public static function strictValidationSet(): array
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

    public function testUnsetHaystackRaisesException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('haystack option is mandatory');

        /** @psalm-suppress InvalidArgument */
        new InArray([]);
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

    public function testNonStrictSafeComparisons(): void
    {
        $validator = new InArray(
            [
                'haystack' => ['test', 0, 'A', 1, 0.0],
            ]
        );

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
                'strict'   => InArray::COMPARE_STRICT,
            ]
        );

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
                'strict'   => InArray::COMPARE_NOT_STRICT,
            ]
        );

        self::assertTrue($validator->isValid('b'));
        self::assertTrue($validator->isValid('a'));
        self::assertTrue($validator->isValid('A'));
        self::assertTrue($validator->isValid('0'));
        self::assertTrue($validator->isValid('1a'));
        self::assertTrue($validator->isValid(0));
    }

    public function testNonStrictSafeComparisonsRecursive(): void
    {
        $validator = new InArray([
            'haystack'  => [
                ['test', 0, 'A', 0.0],
                ['foo', 1, 'a', 'c'],
            ],
            'recursive' => true,
        ]);

        self::assertFalse($validator->isValid('b'));
        self::assertTrue($validator->isValid('a'));
        self::assertTrue($validator->isValid('A'));
        self::assertTrue($validator->isValid('0'));
        self::assertFalse($validator->isValid('1a'));
        self::assertTrue($validator->isValid(0));
    }

    public function testStrictComparisonsRecursive(): void
    {
        $validator = new InArray([
            'haystack'  => [
                ['test', 0, 'A', 0.0],
                ['foo', 1, 'a', 'c'],
            ],
            'strict'    => InArray::COMPARE_STRICT,
            'recursive' => true,
        ]);

        self::assertFalse($validator->isValid('b'));
        self::assertTrue($validator->isValid('a'));
        self::assertTrue($validator->isValid('A'));
        self::assertFalse($validator->isValid('0'));
        self::assertFalse($validator->isValid('1a'));
        self::assertTrue($validator->isValid(0));
    }

    public function testNonStrictComparisonsRecursive(): void
    {
        $validator = new InArray([
            'haystack'  => [
                ['test', 0, 'A', 0.0],
                ['foo', 1, 'a', 'c'],
            ],
            'strict'    => InArray::COMPARE_NOT_STRICT,
            'recursive' => true,
        ]);

        self::assertFalse($validator->isValid('b'));
        self::assertTrue($validator->isValid('a'));
        self::assertTrue($validator->isValid('A'));
        self::assertTrue($validator->isValid('0'));
        self::assertFalse($validator->isValid('1a'));
        self::assertTrue($validator->isValid(0));
    }

    public function testIntegerInputAndStringInHaystack(): void
    {
        $validator = new InArray([
            'haystack' => ['test', 1, 2],
            'strict'   => InArray::COMPARE_NOT_STRICT_AND_PREVENT_STR_TO_INT_VULNERABILITY,
        ]);

        self::assertFalse($validator->isValid(0));

        $validator = new InArray([
            'haystack' => ['test', 1, 2],
            'strict'   => InArray::COMPARE_NOT_STRICT,
        ]);

        self::assertTrue($validator->isValid(0));

        $validator = new InArray([
            'haystack' => ['test', 1, 2],
            'strict'   => InArray::COMPARE_STRICT,
        ]);

        self::assertFalse($validator->isValid(0));
    }

    public function testFloatInputAndStringInHaystack(): void
    {
        $validator = new InArray([
            'haystack' => ['test', 1, 2],
            'strict'   => InArray::COMPARE_NOT_STRICT_AND_PREVENT_STR_TO_INT_VULNERABILITY,
        ]);

        self::assertFalse($validator->isValid(0.0));

        $validator = new InArray([
            'haystack' => ['test', 1, 2],
            'strict'   => InArray::COMPARE_NOT_STRICT,
        ]);

        self::assertTrue($validator->isValid(0.0));

        $validator = new InArray([
            'haystack' => ['test', 1, 2],
            'strict'   => InArray::COMPARE_STRICT,
        ]);

        self::assertFalse($validator->isValid(0.0));
    }

    public function testNumberStringInputAgainstNumberInHaystack(): void
    {
        $validator = new InArray([
            'haystack' => [1, 2],
            'strict'   => InArray::COMPARE_NOT_STRICT_AND_PREVENT_STR_TO_INT_VULNERABILITY,
        ]);

        self::assertFalse($validator->isValid('1asdf'));

        $validator = new InArray([
            'haystack' => [1, 2],
            'strict'   => InArray::COMPARE_NOT_STRICT,
        ]);

        self::assertTrue($validator->isValid('1asdf'));

        $validator = new InArray([
            'haystack' => [1, 2],
            'strict'   => InArray::COMPARE_STRICT,
        ]);

        self::assertFalse($validator->isValid('1asdf'));
    }

    public function testFloatStringInputAgainstNumberInHaystack(): void
    {
        $validator = new InArray([
            'haystack' => [1.5, 2.4],
            'strict'   => InArray::COMPARE_NOT_STRICT_AND_PREVENT_STR_TO_INT_VULNERABILITY,
        ]);

        self::assertFalse($validator->isValid('1.5asdf'));

        $validator = new InArray([
            'haystack' => [1.5, 2.4],
            'strict'   => InArray::COMPARE_NOT_STRICT,
        ]);

        self::assertTrue($validator->isValid('1.5asdf'));

        $validator = new InArray([
            'haystack' => [1.5, 2.4],
            'strict'   => InArray::COMPARE_STRICT,
        ]);

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

        self::assertTrue($validator->isValid(0));
        self::assertFalse($validator->isValid('0'));
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
     */
    #[DataProvider('strictValidationSet')]
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
     */
    #[DataProvider('nonStrictValidationSet')]
    public function testBooleanNotStrictEnforcesNonStrictMode(array $haystack, mixed $valid, mixed $invalid): void
    {
        $validator = new InArray([
            'haystack' => $haystack,
            'strict'   => false,
        ]);

        self::assertTrue($validator->isValid($valid));
        self::assertFalse($validator->isValid($invalid));
    }
}
