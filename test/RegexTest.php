<?php

declare(strict_types=1);

namespace LaminasTest\Validator;

use Laminas\Validator\Exception\InvalidArgumentException;
use Laminas\Validator\Regex;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

use function array_keys;
use function implode;

/**
 * @group      Laminas_Validator
 */
class RegexTest extends TestCase
{
    /**
     * Ensures that the validator follows expected behavior
     *
     * @dataProvider basicDataProvider
     * @return void
     */
    public function testBasic(array $options, string $input, bool $expected)
    {
        $validator = new Regex(...$options);
        $this->assertSame($expected, $validator->isValid($input));
    }

    /**
     * @psalm-return array<string, array{
     *     0: string[]|array<array-key, array<string, string>>,
     *     1: string,
     *     2: bool
     * }>
     */
    public function basicDataProvider(): array
    {
        return [
            // phpcs:disable
            'valid; abc123' => [['/[a-z]/'], 'abc123', true],
            'valid; foo'    => [['/[a-z]/'], 'foo',    true],
            'valid; a'      => [['/[a-z]/'], 'a',      true],
            'valid; z'      => [['/[a-z]/'], 'z',      true],

            'valid; 123' => [['/[a-z]/'], '123', false],
            'valid; A'   => [['/[a-z]/'], 'A',   false],

            'valid; abc123; array' => [[['pattern' => '/[a-z]/']], 'abc123', true],
            'valid; foo; array'    => [[['pattern' => '/[a-z]/']], 'foo', true],
            'valid; a; array'      => [[['pattern' => '/[a-z]/']], 'a', true],
            'valid; z; array'      => [[['pattern' => '/[a-z]/']], 'z', true],

            'valid; 123; array' => [[['pattern' => '/[a-z]/']], '123', false],
            'valid; A; array'   => [[['pattern' => '/[a-z]/']], 'A', false],
            // phpcs:enable
        ];
    }

    /**
     * Ensures that getMessages() returns expected default value
     *
     * @return void
     */
    public function testGetMessages()
    {
        $validator = new Regex('/./');
        $this->assertEquals([], $validator->getMessages());
    }

    /**
     * Ensures that getPattern() returns expected value
     *
     * @return void
     */
    public function testGetPattern()
    {
        $validator = new Regex('/./');
        $this->assertEquals('/./', $validator->getPattern());
    }

    /**
     * Ensures that a bad pattern results in a thrown exception upon isValid() call
     *
     * @return void
     */
    public function testBadPattern()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Internal error parsing');
        new Regex('/');
    }

    /**
     * @Laminas-4352
     */
    public function testNonStringValidation(): void
    {
        $validator = new Regex('/./');
        $this->assertFalse($validator->isValid([1 => 1]));
    }

    /**
     * @Laminas-11863
     * @dataProvider specialCharValidationProvider
     */
    public function testSpecialCharValidation(bool $expected, string $input): void
    {
        $validator = new Regex('/^[[:alpha:]\']+$/iu');
        $this->assertEquals(
            $expected,
            $validator->isValid($input),
            'Reason: ' . implode('', $validator->getMessages())
        );
    }

    /**
     * The elements of each array are, in order:
     *      - expected validation result
     *      - test input value
     *
     * @psalm-return array<array-key, array{0: bool, 1: string}>
     */
    public function specialCharValidationProvider(): array
    {
        return [
            [true, 'test'],
            [true, 'òèùtestòò'],
            [true, 'testà'],
            [true, 'teààst'],
            [true, 'ààòòìùéé'],
            [true, 'èùòìiieeà'],
            [false, 'test99'],
        ];
    }

    public function testEqualsMessageTemplates(): void
    {
        $validator = new Regex('//');
        $this->assertSame(
            [
                Regex::INVALID,
                Regex::NOT_MATCH,
                Regex::ERROROUS,
            ],
            array_keys($validator->getMessageTemplates())
        );
        $this->assertEquals($validator->getOption('messageTemplates'), $validator->getMessageTemplates());
    }

    public function testEqualsMessageVariables(): void
    {
        $validator        = new Regex('//');
        $messageVariables = [
            'pattern' => 'pattern',
        ];
        $this->assertSame($messageVariables, $validator->getOption('messageVariables'));
    }

    /**
     * @psalm-return array<string, array{0: mixed}>
     */
    public function invalidConstructorArgumentsProvider(): array
    {
        return [
            'true'       => [true],
            'false'      => [false],
            'zero'       => [0],
            'int'        => [1],
            'zero-float' => [0.0],
            'float'      => [1.0],
            'object'     => [(object) []],
        ];
    }

    /**
     * @dataProvider invalidConstructorArgumentsProvider
     * @param mixed $options
     */
    public function testConstructorRaisesExceptionWhenProvidedInvalidArguments($options): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Regex($options);
    }

    public function testConstructorRaisesExceptionWhenProvidedWithInvalidOptionsArray(): void
    {
        $options = ['foo' => 'bar'];
        $this->expectException(InvalidArgumentException::class);
        new Regex($options);
    }

    public function testIsValidShouldReturnFalseWhenRegexPatternIsInvalid(): void
    {
        $validator = new Regex('//');
        $pattern   = '/';

        $r = new ReflectionProperty($validator, 'pattern');
        $r->setAccessible(true);
        $r->setValue($validator, $pattern);

        $this->assertFalse($validator->isValid('test'));
    }
}
