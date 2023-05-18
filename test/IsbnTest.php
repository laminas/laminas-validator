<?php

declare(strict_types=1);

namespace LaminasTest\Validator;

use Laminas\Validator\Exception\InvalidArgumentException;
use Laminas\Validator\Isbn;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

use function array_keys;

final class IsbnTest extends TestCase
{
    /**
     * @psalm-return array<string, array{
     *     0: string,
     *     1: bool
     * }>
     */
    public static function basicProvider(): array
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
     */
    #[DataProvider('basicProvider')]
    public function testBasic(string $value, bool $expected): void
    {
        $validator = new Isbn();

        self::assertSame($expected, $validator->isValid($value));
    }

    /**
     * Ensures that setSeparator() works as expected
     */
    public function testType(): void
    {
        $validator = new Isbn();

        $validator->setType(Isbn::AUTO);
        self::assertSame(Isbn::AUTO, $validator->getType());

        $validator->setType(Isbn::ISBN10);
        self::assertSame(Isbn::ISBN10, $validator->getType());

        $validator->setType(Isbn::ISBN13);
        self::assertSame(Isbn::ISBN13, $validator->getType());

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid ISBN type');

        $validator->setType('X');
    }

    /**
     * Ensures that setSeparator() works as expected
     */
    public function testSeparator(): void
    {
        $validator = new Isbn();

        $validator->setSeparator('-');
        self::assertSame('-', $validator->getSeparator());

        $validator->setSeparator(' ');
        self::assertSame(' ', $validator->getSeparator());

        $validator->setSeparator('');
        self::assertSame('', $validator->getSeparator());

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid ISBN separator');

        $validator->setSeparator('X');
    }

    /**
     * Ensures that __construct() works as expected
     */
    public function testInitialization(): void
    {
        $options   = [
            'type'      => Isbn::AUTO,
            'separator' => ' ',
        ];
        $validator = new Isbn($options);

        self::assertSame(Isbn::AUTO, $validator->getType());
        self::assertSame(' ', $validator->getSeparator());

        $options   = [
            'type'      => Isbn::ISBN10,
            'separator' => '-',
        ];
        $validator = new Isbn($options);

        self::assertSame(Isbn::ISBN10, $validator->getType());
        self::assertSame('-', $validator->getSeparator());

        $options   = [
            'type'      => Isbn::ISBN13,
            'separator' => '',
        ];
        $validator = new Isbn($options);

        self::assertSame(Isbn::ISBN13, $validator->getType());
        self::assertSame('', $validator->getSeparator());
    }

    /**
     * Ensures that the validator follows expected behavior
     */
    public function testTypeAuto(): void
    {
        $validator = new Isbn();

        self::assertTrue($validator->isValid('0060929871'));
        self::assertFalse($validator->isValid('0-06-092987-1'));
        self::assertFalse($validator->isValid('0 06 092987 1'));

        self::assertTrue($validator->isValid('9780555023402'));
        self::assertFalse($validator->isValid('978-0-555023-40-2'));
        self::assertFalse($validator->isValid('978 0 555023 40 2'));

        $validator->setSeparator('-');

        self::assertFalse($validator->isValid('0060929871'));
        self::assertTrue($validator->isValid('0-06-092987-1'));
        self::assertFalse($validator->isValid('0 06 092987 1'));

        self::assertFalse($validator->isValid('9780555023402'));
        self::assertTrue($validator->isValid('978-0-555023-40-2'));
        self::assertFalse($validator->isValid('978 0 555023 40 2'));

        $validator->setSeparator(' ');

        self::assertFalse($validator->isValid('0060929871'));
        self::assertFalse($validator->isValid('0-06-092987-1'));
        self::assertTrue($validator->isValid('0 06 092987 1'));

        self::assertFalse($validator->isValid('9780555023402'));
        self::assertFalse($validator->isValid('978-0-555023-40-2'));
        self::assertTrue($validator->isValid('978 0 555023 40 2'));
    }

    /**
     * Ensures that the validator follows expected behavior
     */
    public function testType10(): void
    {
        $validator = new Isbn();
        $validator->setType(Isbn::ISBN10);

        self::assertTrue($validator->isValid('0060929871'));
        self::assertFalse($validator->isValid('9780555023402'));

        $validator->setSeparator('-');

        self::assertTrue($validator->isValid('0-06-092987-1'));
        self::assertFalse($validator->isValid('978-0-555023-40-2'));

        $validator->setSeparator(' ');

        self::assertTrue($validator->isValid('0 06 092987 1'));
        self::assertFalse($validator->isValid('978 0 555023 40 2'));
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

        self::assertFalse($validator->isValid('0060929871'));
        self::assertTrue($validator->isValid('9780555023402'));

        $validator->setSeparator('-');

        self::assertFalse($validator->isValid('0-06-092987-1'));
        self::assertTrue($validator->isValid('978-0-555023-40-2'));

        $validator->setSeparator(' ');

        self::assertFalse($validator->isValid('0 06 092987 1'));
        self::assertTrue($validator->isValid('978 0 555023 40 2'));
    }

    #[Group('Laminas-9605')]
    public function testInvalidTypeGiven(): void
    {
        $validator = new Isbn();
        $validator->setType(Isbn::ISBN13);

        self::assertFalse($validator->isValid((float) 1.2345));
        self::assertFalse($validator->isValid((object) 'Test'));
    }

    public function testEqualsMessageTemplates(): void
    {
        $validator = new Isbn();

        self::assertSame(
            [
                Isbn::INVALID,
                Isbn::NO_ISBN,
            ],
            array_keys($validator->getMessageTemplates())
        );
        self::assertSame($validator->getOption('messageTemplates'), $validator->getMessageTemplates());
    }
}
