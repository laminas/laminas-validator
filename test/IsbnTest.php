<?php

declare(strict_types=1);

namespace LaminasTest\Validator;

use Laminas\Validator\Exception\InvalidArgumentException;
use Laminas\Validator\Isbn;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

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
            'Zend Framework In Action (10)'                                 => ['9781638355144', true],
            'Zend Framework In Action (13)'                                 => ['978-1933988320', true],
            'Domain Driven Design (10)'                                     => ['0321125215', true],
            'Domain Driven Design (13)'                                     => ['978-0321125217', true],
        ];
    }

    #[DataProvider('basicProvider')]
    public function testBasic(string $value, bool $expected): void
    {
        $validator = new Isbn();

        self::assertSame($expected, $validator->isValid($value));
    }

    public function testInvalidTypeIsExceptional(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid ISBN type');

        /** @psalm-suppress InvalidArgument */
        new Isbn([
            'type' => 'Foo',
        ]);
    }

    /** @return array<string, array{0: mixed, 1: bool, 2: bool, 3: bool}> */
    public static function isbnValueProvider(): array
    {
        return [
            '10 - No separator'    => ['0060929871', true, true, false],
            '10 - Dash separator'  => ['0-06-092987-1', true, true, false],
            '10 - Space separator' => ['0 06 092987 1', true, true, false],
            '13 - No separator'    => ['9780555023402', true, false, true],
            '13 - Dash separator'  => ['978-0-555023-40-2', true, false, true],
            '13 - Space separator' => ['978 0 555023 40 2', true, false, true],
            'Float'                => [1.234, false, false, false],
            'String'               => ['whatever', false, false, false],
            'Short Int'            => [123, false, false, false],
            'Array'                => [['foo'], false, false, false],
        ];
    }

    #[DataProvider('isbnValueProvider')]
    public function testValidationPerTypeOption(mixed $value, bool $validAuto, bool $valid10, bool $valid13): void
    {
        $validator = new Isbn();
        self::assertSame($validAuto, $validator->isValid($value));

        $validator = new Isbn(['type' => Isbn::ISBN10]);
        self::assertSame($valid10, $validator->isValid($value));

        $validator = new Isbn(['type' => Isbn::ISBN13]);
        self::assertSame($valid13, $validator->isValid($value));
    }
}
