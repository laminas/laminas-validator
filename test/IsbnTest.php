<?php

namespace LaminasTest\Validator;

use Laminas\Validator\Exception\InvalidArgumentException;
use Laminas\Validator\Isbn;
use PHPUnit\Framework\TestCase;

/**
 * @group      Laminas_Validator
 */
class IsbnTest extends TestCase
{
    /**
     * @psalm-return array<string, array{
     *     0: string,
     *     1: bool
     * }>
     */
    public function basicProvider(): array
    {
        return [
            'Brave New World by Aldous Huxley - True'                       => ['0060929871', true],
            'Brave New World by Aldous Huxley - False'                      => ['006092987X', false],
            'Time Rations by Benjamin Friedlander - True'                   => ['188202205X', true],
            'Time Rations by Benjamin Friedlander - False'                  => ['1882022059', false],
            'Towards The Primeval Lighting Field by Will Alexander - True'  => ['1882022300', true],
            'Towards The Primeval Lighting Field by Will Alexander - False' => ['1882022301', false],
            'ISBN-13 for dummies by Zoë Wykes - True'                       => ['9780555023402', true],
            'ISBN-13 for dummies by Zoë Wykes - False'                      => ['97805550234029', false],
            'Change Your Brain, Change Your Life Daniel G. Amen - True'     => ['9780812929980', true],
            'Change Your Brain, Change Your Life Daniel G. Amen - False'    => ['9780812929981', false],
        ];
    }

    /**
     * Ensures that the validator follows expected behavior
     *
     * @dataProvider basicProvider
     */
    public function testBasic(string $value, bool $expected): void
    {
        $validator = new Isbn();
        $this->assertSame($expected, $validator->isValid($value));
    }

    /**
     * Ensures that setSeparator() works as expected
     *
     * @return void
     */
    public function testType()
    {
        $validator = new Isbn();

        $validator->setType(Isbn::AUTO);
        $this->assertEquals(Isbn::AUTO, $validator->getType());

        $validator->setType(Isbn::ISBN10);
        $this->assertEquals(Isbn::ISBN10, $validator->getType());

        $validator->setType(Isbn::ISBN13);
        $this->assertEquals(Isbn::ISBN13, $validator->getType());

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid ISBN type');
        $validator->setType('X');
    }

    /**
     * Ensures that setSeparator() works as expected
     *
     * @return void
     */
    public function testSeparator()
    {
        $validator = new Isbn();

        $validator->setSeparator('-');
        $this->assertEquals('-', $validator->getSeparator());

        $validator->setSeparator(' ');
        $this->assertEquals(' ', $validator->getSeparator());

        $validator->setSeparator('');
        $this->assertEquals('', $validator->getSeparator());

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid ISBN separator');
        $validator->setSeparator('X');
    }

    /**
     * Ensures that __construct() works as expected
     *
     * @return void
     */
    public function testInitialization()
    {
        $options   = [
            'type'      => Isbn::AUTO,
            'separator' => ' ',
        ];
        $validator = new Isbn($options);
        $this->assertEquals(Isbn::AUTO, $validator->getType());
        $this->assertEquals(' ', $validator->getSeparator());

        $options   = [
            'type'      => Isbn::ISBN10,
            'separator' => '-',
        ];
        $validator = new Isbn($options);
        $this->assertEquals(Isbn::ISBN10, $validator->getType());
        $this->assertEquals('-', $validator->getSeparator());

        $options   = [
            'type'      => Isbn::ISBN13,
            'separator' => '',
        ];
        $validator = new Isbn($options);
        $this->assertEquals(Isbn::ISBN13, $validator->getType());
        $this->assertEquals('', $validator->getSeparator());
    }

    /**
     * Ensures that the validator follows expected behavior
     *
     * @return void
     */
    public function testTypeAuto()
    {
        $validator = new Isbn();

        $this->assertTrue($validator->isValid('0060929871'));
        $this->assertFalse($validator->isValid('0-06-092987-1'));
        $this->assertFalse($validator->isValid('0 06 092987 1'));

        $this->assertTrue($validator->isValid('9780555023402'));
        $this->assertFalse($validator->isValid('978-0-555023-40-2'));
        $this->assertFalse($validator->isValid('978 0 555023 40 2'));

        $validator->setSeparator('-');

        $this->assertFalse($validator->isValid('0060929871'));
        $this->assertTrue($validator->isValid('0-06-092987-1'));
        $this->assertFalse($validator->isValid('0 06 092987 1'));

        $this->assertFalse($validator->isValid('9780555023402'));
        $this->assertTrue($validator->isValid('978-0-555023-40-2'));
        $this->assertFalse($validator->isValid('978 0 555023 40 2'));

        $validator->setSeparator(' ');

        $this->assertFalse($validator->isValid('0060929871'));
        $this->assertFalse($validator->isValid('0-06-092987-1'));
        $this->assertTrue($validator->isValid('0 06 092987 1'));

        $this->assertFalse($validator->isValid('9780555023402'));
        $this->assertFalse($validator->isValid('978-0-555023-40-2'));
        $this->assertTrue($validator->isValid('978 0 555023 40 2'));
    }

    /**
     * Ensures that the validator follows expected behavior
     *
     * @return void
     */
    public function testType10()
    {
        $validator = new Isbn();
        $validator->setType(Isbn::ISBN10);

        $this->assertTrue($validator->isValid('0060929871'));
        $this->assertFalse($validator->isValid('9780555023402'));

        $validator->setSeparator('-');

        $this->assertTrue($validator->isValid('0-06-092987-1'));
        $this->assertFalse($validator->isValid('978-0-555023-40-2'));

        $validator->setSeparator(' ');

        $this->assertTrue($validator->isValid('0 06 092987 1'));
        $this->assertFalse($validator->isValid('978 0 555023 40 2'));
    }

    /**
     * Ensures that the validator follows expected behavior
     *
     * @return void
     */
    public function testType13()
    {
        $validator = new Isbn();
        $validator->setType(Isbn::ISBN13);

        $this->assertFalse($validator->isValid('0060929871'));
        $this->assertTrue($validator->isValid('9780555023402'));

        $validator->setSeparator('-');

        $this->assertFalse($validator->isValid('0-06-092987-1'));
        $this->assertTrue($validator->isValid('978-0-555023-40-2'));

        $validator->setSeparator(' ');

        $this->assertFalse($validator->isValid('0 06 092987 1'));
        $this->assertTrue($validator->isValid('978 0 555023 40 2'));
    }

    /**
     * @group Laminas-9605
     */
    public function testInvalidTypeGiven(): void
    {
        $validator = new Isbn();
        $validator->setType(Isbn::ISBN13);

        $this->assertFalse($validator->isValid((float) 1.2345));
        $this->assertFalse($validator->isValid((object) 'Test'));
    }

    public function testEqualsMessageTemplates(): void
    {
        $validator = new Isbn();
        $this->assertObjectHasAttribute('messageTemplates', $validator);
        $this->assertEquals($validator->getOption('messageTemplates'), $validator->getMessageTemplates());
    }
}
