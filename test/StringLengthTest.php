<?php

/**
 * @see       https://github.com/laminas/laminas-validator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-validator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-validator/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Validator;

use Laminas\Validator\Exception\InvalidArgumentException;
use Laminas\Validator\StringLength;
use PHPUnit\Framework\TestCase;

/**
 * @group      Laminas_Validator
 */
class StringLengthTest extends TestCase
{
    /**
     * @var StringLength
     */
    protected $validator;

    /**
     * Creates a new StringLength object for each test method
     */
    protected function setUp() : void
    {
        $this->validator = new StringLength();
    }

    /**
     * Ensures that the validator follows expected behavior
     *
     * @dataProvider basicDataProvider
     * @return void
     */
    public function testBasic(array $options, bool $expected, string $input)
    {
        ini_set('default_charset', 'UTF-8');

        $validator = new StringLength(...$options);
        $this->assertSame($expected, $validator->isValid($input));
    }

    /**
     * @psalm-return array<string, array{0: array, 1: bool, 2: string}>
     */
    public function basicDataProvider(): array
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
     *
     * @return void
     */
    public function testGetMessages()
    {
        $this->assertEquals([], $this->validator->getMessages());
    }

    /**
     * Ensures that getMin() returns expected default value
     *
     * @return void
     */
    public function testGetMin()
    {
        $this->assertEquals(0, $this->validator->getMin());
    }

    /**
     * Ensures that getMax() returns expected default value
     *
     * @return void
     */
    public function testGetMax()
    {
        $this->assertEquals(null, $this->validator->getMax());
    }

    /**
     * Ensures that setMin() throws an exception when given a value greater than the maximum
     *
     * @return void
     */
    public function testSetMinExceptionGreaterThanMax()
    {
        $max = 1;
        $min = 2;

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The minimum must be less than or equal to the maximum length, but');
        $this->validator->setMax($max)->setMin($min);
    }

    /**
     * Ensures that setMax() throws an exception when given a value less than the minimum
     *
     * @return void
     */
    public function testSetMaxExceptionLessThanMin()
    {
        $max = 1;
        $min = 2;

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The maximum must be greater than or equal to the minimum length, but ');
        $this->validator->setMin($min)->setMax($max);
    }

    /**
     * @return void
     */
    public function testDifferentEncodingWithValidator()
    {
        ini_set('default_charset', 'UTF-8');

        $validator = new StringLength(2, 2, 'UTF-8');
        $this->assertEquals(true, $validator->isValid('ab'));

        $this->assertEquals('UTF-8', $validator->getEncoding());
        $validator->setEncoding('ISO-8859-1');
        $this->assertEquals('ISO-8859-1', $validator->getEncoding());
    }

    /**
     * @Laminas-4352
     *
     * @return void
     */
    public function testNonStringValidation(): void
    {
        $this->assertFalse($this->validator->isValid([1 => 1]));
    }

    public function testEqualsMessageTemplates(): void
    {
        $validator = $this->validator;
        $this->assertObjectHasAttribute('messageTemplates', $validator);
        $this->assertEquals($validator->getOption('messageTemplates'), $validator->getMessageTemplates());
    }

    public function testEqualsMessageVariables(): void
    {
        $validator = $this->validator;
        $this->assertObjectHasAttribute('messageVariables', $validator);
        $this->assertEquals(array_keys($validator->getOption('messageVariables')), $validator->getMessageVariables());
    }
}
