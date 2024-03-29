<?php

declare(strict_types=1);

namespace LaminasTest\Validator;

use Laminas\Validator\Exception\InvalidArgumentException;
use Laminas\Validator\StringLength;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use function array_keys;
use function ini_set;

final class StringLengthTest extends TestCase
{
    private StringLength $validator;

    /**
     * Creates a new StringLength object for each test method
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = new StringLength();
    }

    /**
     * Ensures that the validator follows expected behavior
     */
    #[DataProvider('basicDataProvider')]
    public function testBasic(array $options, bool $expected, string $input): void
    {
        ini_set('default_charset', 'UTF-8');

        $validator = new StringLength(...$options);

        self::assertSame($expected, $validator->isValid($input));
    }

    /**
     * @psalm-return array<string, array{0: array, 1: bool, 2: string}>
     */
    public static function basicDataProvider(): array
    {
        return [
            // phpcs:disable
            'valid; minimum: 0; maximum: null; ""' => [[0, null], true, ''],
            'valid; minimum: 0; maximum: null; a'  => [[0, null], true, 'a'],
            'valid; minimum: 0; maximum: null; ab' => [[0, null], true, 'ab'],

            'valid; minimum: 0; no maximum; ""' => [[0], true, ''],
            'valid; minimum: 0; no maximum; a'  => [[0], true, 'a'],
            'valid; minimum: 0; no maximum; ab' => [[0], true, 'ab'],

            'valid; minimum: -1; maximum: null; ""' => [[-1, null], true, ''],

            'valid; minimum: 2; maximum: 2; ab'   => [[2, 2], true, 'ab'],
            'valid; minimum: 2; maximum: 2; "  "' => [[2, 2], true, '  '],

            'valid; minimum: 2; maximum: 2; a'   => [[2, 2], false, 'a'],
            'valid; minimum: 2; maximum: 2; abc' => [[2, 2], false, 'abc'],

            'invalid; minimum: 1; maximum: null; ""' => [[1, null],   false,  ''],

            'valid; minimum: 2; maximum: 3; ab'  => [[2, 3], true, 'ab'],
            'valid; minimum: 2; maximum: 3; abc' => [[2, 3], true, 'abc'],

            'invalid; minimum: 2; maximum: 3; a'    => [[2, 3], false, 'a'],
            'invalid; minimum: 2; maximum: 3; abcd' => [[2, 3], false, 'abcd'],

            'valid; minimum: 3; maximum: 3; äöü'       => [[3, 3],    true, 'äöü'],
            'valid; minimum: 6; maximum: 6; Müller'    => [[6, 6],    true, 'Müller'],
            'valid; minimum: null; maximum: 6; Müller' => [[null, 6], true,'Müller'],
            // phpcs:enable
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
     * Ensures that getMin() returns expected default value
     */
    public function testGetMin(): void
    {
        self::assertSame(0, $this->validator->getMin());
    }

    /**
     * Ensures that getMax() returns expected default value
     */
    public function testGetMax(): void
    {
        self::assertNull($this->validator->getMax());
    }

    /**
     * Ensures that setMin() throws an exception when given a value greater than the maximum
     */
    public function testSetMinExceptionGreaterThanMax(): void
    {
        $max = 1;
        $min = 2;

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The minimum must be less than or equal to the maximum length, but');

        $this->validator->setMax($max)->setMin($min);
    }

    /**
     * Ensures that setMax() throws an exception when given a value less than the minimum
     */
    public function testSetMaxExceptionLessThanMin(): void
    {
        $max = 1;
        $min = 2;

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The maximum must be greater than or equal to the minimum length, but ');

        $this->validator->setMin($min)->setMax($max);
    }

    public function testDifferentEncodingWithValidator(): void
    {
        ini_set('default_charset', 'UTF-8');

        $validator = new StringLength(2, 2, 'UTF-8');

        self::assertTrue($validator->isValid('ab'));
        self::assertSame('UTF-8', $validator->getEncoding());

        $validator->setEncoding('ISO-8859-1');

        self::assertSame('ISO-8859-1', $validator->getEncoding());
    }

    /**
     * @Laminas-4352
     */
    public function testNonStringValidation(): void
    {
        self::assertFalse($this->validator->isValid([1 => 1]));
    }

    public function testEqualsMessageTemplates(): void
    {
        self::assertSame(
            [
                StringLength::INVALID,
                StringLength::TOO_SHORT,
                StringLength::TOO_LONG,
            ],
            array_keys($this->validator->getMessageTemplates())
        );
        self::assertSame($this->validator->getOption('messageTemplates'), $this->validator->getMessageTemplates());
    }

    public function testEqualsMessageVariables(): void
    {
        $messageVariables = [
            'min'    => ['options' => 'min'],
            'max'    => ['options' => 'max'],
            'length' => ['options' => 'length'],
        ];

        self::assertSame($messageVariables, $this->validator->getOption('messageVariables'));
        self::assertSame(array_keys($messageVariables), $this->validator->getMessageVariables());
    }
}
