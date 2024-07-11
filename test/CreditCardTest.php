<?php

declare(strict_types=1);

namespace LaminasTest\Validator;

use Laminas\Validator\CreditCard;
use Laminas\Validator\Exception\InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

use function current;

final class CreditCardTest extends TestCase
{
    /**
     * @psalm-return array<array-key, array{0: string, 1: bool}>
     */
    public static function basicValues(): array
    {
        return [
            ['4111111111111111', true],
            ['5404000000000001', true],
            ['374200000000004', true],
            ['4444555566667777', false],
            ['ABCDEF', false],
        ];
    }

    /**
     * Ensures that the validator follows expected behavior
     */
    #[DataProvider('basicValues')]
    public function testBasic(string $input, bool $expected): void
    {
        $validator = new CreditCard();

        self::assertSame($expected, $validator->isValid($input));
    }

    /**
     * Ensures that getMessages() returns expected default value
     */
    public function testGetMessages(): void
    {
        $validator = new CreditCard();

        self::assertSame([], $validator->getMessages());
    }

    /**
     * @psalm-return array<array-key, array{0: string, 1: bool}>
     */
    public static function visaValues(): array
    {
        return [
            ['4111111111111111', true],
            ['5404000000000001', false],
            ['374200000000004', false],
            ['4444555566667777', false],
            ['ABCDEF', false],
        ];
    }

    /**
     * Test specific provider
     */
    #[DataProvider('visaValues')]
    public function testProvider(string $input, bool $expected): void
    {
        $validator = new CreditCard(['type' => CreditCard::VISA]);
        self::assertSame($expected, $validator->isValid($input));

        $validator = new CreditCard(['type' => [CreditCard::VISA]]);
        self::assertSame($expected, $validator->isValid($input));

        /** @psalm-suppress InvalidArgument - We allow case-insensitive match of card names */
        $validator = new CreditCard(['type' => 'ViSa']);
        self::assertSame($expected, $validator->isValid($input));

        /** @psalm-suppress InvalidArgument - We allow case-insensitive match of card names */
        $validator = new CreditCard(['type' => ['ViSa']]);
        self::assertSame($expected, $validator->isValid($input));
    }

    /**
     * Test non string input
     */
    public function testIsValidWithNonString(): void
    {
        $validator = new CreditCard(['type' => CreditCard::VISA]);

        self::assertFalse($validator->isValid(['something']));
    }

    /**
     * @psalm-return array<array-key, array{0: string, 1: bool}>
     */
    public static function serviceValues(): array
    {
        return [
            ['4111111111111111', false],
            ['5404000000000001', false],
            ['374200000000004', false],
            ['4444555566667777', false],
            ['ABCDEF', false],
        ];
    }

    /**
     * Test service class with invalid validation
     */
    #[DataProvider('serviceValues')]
    public function testServiceClass(string $input, bool $expected): void
    {
        // phpcs:disable WebimpressCodingStandard.NamingConventions
        $validator = new CreditCard([
            'service' => static fn(mixed $_): bool => false,
        ]);

        self::assertSame($expected, $validator->isValid($input));
    }

    /**
     * @psalm-return array<array-key, array{0: string, 1: bool}>
     */
    public static function optionsValues(): array
    {
        return [
            ['4111111111111111', false],
            ['5404000000000001', false],
            ['374200000000004', false],
            ['4444555566667777', false],
            ['ABCDEF', false],
        ];
    }

    /**
     * Test non string input
     */
    #[DataProvider('optionsValues')]
    public function testConstructionWithOptions(string $input, bool $expected): void
    {
        // phpcs:disable WebimpressCodingStandard.NamingConventions
        $validator = new CreditCard([
            'type'    => CreditCard::VISA,
            'service' => static fn(mixed $_): bool => false,
        ]);

        self::assertSame($expected, $validator->isValid($input));
    }

    /**
     * Data provider
     *
     * @psalm-return array<array-key, array{0: string, 1: bool}>
     */
    public static function jcbValues(): array
    {
        return [
            ['3566003566003566', true],
            ['3528000000000007', true],
            ['3528000000000007', true],
            ['3528000000000007', true],
            ['3088185545477406', false],
            ['3158854390756173', false],
            ['3088936920428541', false],
            ['213193692042852', true],
            ['180012362524156', true],
        ];
    }

    /**
     * Test JCB number validity
     *
     * @param string $input
     * @param bool   $expected
     */
    #[DataProvider('jcbValues')]
    #[Group('6278')]
    #[Group('6927')]
    public function testJcbCard($input, $expected): void
    {
        $validator = new CreditCard(['type' => CreditCard::JCB]);

        self::assertSame($expected, $validator->isValid($input));
    }

    /**
     * Data provider
     *
     * @psalm-return array<array-key, array{0: string, 1: bool}>
     */
    public static function mastercardValues(): array
    {
        return [
            ['4111111111111111', false],
            ['5011642326344731', false],
            ['5130982099822729', true],
            ['2220993834549400', false],
            ['2221006548643366', true],
            ['2222007329134574', true],
            ['2393923057923090', true],
            ['2484350479254492', true],
            ['2518224476613101', true],
            ['2659969950495289', true],
            ['2720992392889757', true],
            ['2721008996056187', false],
        ];
    }

    /**
     * Test mastercard number validity
     *
     * @param string $input
     * @param bool   $expected
     */
    #[DataProvider('mastercardValues')]
    public function testMastercardCard($input, $expected): void
    {
        $validator = new CreditCard(['type' => CreditCard::MASTERCARD]);

        self::assertSame($expected, $validator->isValid($input));
    }

    /**
     * Data provider
     *
     * @psalm-return array<array-key, array{0: string, 1: bool}>
     */
    public static function mirValues(): array
    {
        return [
            ['3011111111111000', false],
            ['2031343323344731', false],
            ['2200312032822721', true],
            ['2209993834549400', false],
            ['2204001882200999', true],
            ['2202000312124573', true],
            ['2203921957923012', true],
            ['2204150479254495', true],
            ['2201123406612104', true],
            ['2900008996056', false],
            ['2201969950494', true],
            ['2201342387927', true],
            ['2205969950494', false],
        ];
    }

    /**
     * Test mir card number validity
     *
     * @param string $input
     * @param bool   $expected
     */
    #[DataProvider('mirValues')]
    public function testMirCard($input, $expected): void
    {
        $validator = new CreditCard(['type' => CreditCard::MIR]);

        self::assertSame($expected, $validator->isValid($input));
    }

    /**
     * Test an invalid service class
     */
    public function testInvalidServiceClass(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid callback given');

        /** @psalm-suppress InvalidArgument */
        new CreditCard([
            'service' => [self::class, 'nocallback'],
        ]);
    }

    #[Group('Laminas-9477')]
    public function testMultiInstitute(): void
    {
        $validator = new CreditCard(['type' => CreditCard::MASTERCARD]);

        self::assertFalse($validator->isValid('4111111111111111'));

        $message = $validator->getMessages();

        self::assertStringContainsString('not from an allowed institute', current($message));
    }

    public function testThatTheCallbackReceivesTheExpectedParameters(): void
    {
        $list = [
            CreditCard::VISA,
            CreditCard::MAESTRO,
        ];

        $input = '4111111111111111';

        $callback = function (string $cardNumber, array $types) use ($list, $input): bool {
            self::assertSame($list, $types);
            self::assertSame($input, $cardNumber);

            return true;
        };

        $validator = new CreditCard([
            'type'    => $list,
            'service' => $callback,
        ]);

        self::assertTrue($validator->isValid($input));
    }
}
