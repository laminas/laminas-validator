<?php

declare(strict_types=1);

namespace LaminasTest\Validator;

use Laminas\Validator\Barcode;
use Laminas\Validator\Barcode\AdapterInterface;
use Laminas\Validator\Barcode\Ean13;
use Laminas\Validator\Exception\InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

final class BarcodeTest extends TestCase
{
    /** @return list<array{0: string|AdapterInterface}> */
    public static function validAdapterOptions(): array
    {
        return [
            [new Barcode\Upca()],
            ['Upca'],
            ['UPCA'],
            ['upca'],
            [Barcode\Upca::class],
        ];
    }

    #[DataProvider('validAdapterOptions')]
    public function testThatAnAdapterInstanceCanBeProvidedToTheConstructor(string|AdapterInterface $adapter): void
    {
        $validator = new Barcode([
            'adapter' => $adapter,
        ]);
        self::assertTrue($validator->isValid('065100004327'));
    }

    public static function invalidAdapterArguments(): array
    {
        return [
            ['Not a hope…'],
            [self::class],
        ];
    }

    #[DataProvider('invalidAdapterArguments')]
    public function testExceptionThrownForInvalidAdapter(string $adapter): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The "adapter" option must resolve to an instance of');

        new Barcode(['adapter' => $adapter]);
    }

    public function testSetAdapter(): void
    {
        $barcode = new Barcode(['adapter' => Barcode\Upca::class]);
        self::assertTrue($barcode->isValid('065100004327'));

        $barcode = new Barcode(['adapter' => Ean13::class]);
        self::assertTrue($barcode->isValid('0075678164125'));
    }

    public function testNonStringValidation(): void
    {
        $barcode = new Barcode(['adapter' => Barcode\Upca::class]);
        self::assertFalse($barcode->isValid(106510000.4327));
        self::assertArrayHasKey(Barcode::INVALID, $barcode->getMessages());

        self::assertFalse($barcode->isValid(['065100004327']));
        self::assertArrayHasKey(Barcode::INVALID, $barcode->getMessages());
    }

    public function testEan13AdapterIsUsedWhenNoAdapterIsProvided(): void
    {
        $barcode = new Barcode();

        self::assertTrue($barcode->isValid('0075678164125'));
    }

    public function testRoyalMailIsValid(): void
    {
        $barcode = new Barcode(['adapter' => Barcode\Royalmail::class, 'useChecksum' => true]);

        self::assertTrue($barcode->isValid('1234562'));
    }

    public function testCODE25(): void
    {
        $barcode = new Barcode([
            'adapter'     => Barcode\Code25::class,
            'useChecksum' => false,
        ]);

        self::assertTrue($barcode->isValid('0123456789101213'));
        self::assertTrue($barcode->isValid('123'));
        self::assertFalse($barcode->isValid('123a'));

        $barcode = new Barcode([
            'adapter'     => Barcode\Code25::class,
            'useChecksum' => true,
        ]);

        self::assertTrue($barcode->isValid('0123456789101214'));
        self::assertFalse($barcode->isValid('0123456789101213'));
    }

    public function testCODE25INTERLEAVED(): void
    {
        $barcode = new Barcode([
            'adapter'     => Barcode\Code25interleaved::class,
            'useChecksum' => false,
        ]);

        self::assertTrue($barcode->isValid('0123456789101213'));
        self::assertFalse($barcode->isValid('123'));

        $barcode = new Barcode([
            'adapter'     => Barcode\Code25interleaved::class,
            'useChecksum' => true,
        ]);

        self::assertTrue($barcode->isValid('0123456789101214'));
        self::assertFalse($barcode->isValid('0123456789101213'));
    }

    public function testCODE39(): void
    {
        $barcode = new Barcode([
            'adapter'     => Barcode\Code39::class,
            'useChecksum' => false,
        ]);

        self::assertTrue($barcode->isValid('TEST93TEST93TEST93TEST93Y+'));
        self::assertTrue($barcode->isValid('00075678164124'));
        self::assertFalse($barcode->isValid('Test93Test93Test'));

        $barcode = new Barcode([
            'adapter'     => Barcode\Code39::class,
            'useChecksum' => true,
        ]);

        self::assertTrue($barcode->isValid('159AZH'));
        self::assertFalse($barcode->isValid('159AZG'));
    }

    public function testCODE39EXT(): void
    {
        $barcode = new Barcode([
            'adapter'     => Barcode\Code39ext::class,
            'useChecksum' => false,
        ]);

        self::assertTrue($barcode->isValid('TEST93TEST93TEST93TEST93Y+'));
        self::assertTrue($barcode->isValid('00075678164124'));
        self::assertTrue($barcode->isValid('Test93Test93Test'));

        // @TODO: CODE39 EXTENDED CHECKSUM VALIDATION MISSING
        // self::assertTrue($barcode->isValid('159AZH'));
        // self::assertFalse($barcode->isValid('159AZG'));
    }

    public function testCODE93(): void
    {
        $barcode = new Barcode([
            'adapter'     => Barcode\Code93::class,
            'useChecksum' => false,
        ]);

        self::assertTrue($barcode->isValid('TEST93+'));
        self::assertFalse($barcode->isValid('Test93+'));

        $barcode = new Barcode([
            'adapter'     => Barcode\Code93::class,
            'useChecksum' => true,
        ]);

        self::assertTrue($barcode->isValid('CODE 93E0'));
        self::assertFalse($barcode->isValid('CODE 93E1'));
    }

    public function testCODE93EXT(): void
    {
        $barcode = new Barcode([
            'adapter'     => Barcode\Code93ext::class,
            'useChecksum' => false,
        ]);

        self::assertTrue($barcode->isValid('TEST93+'));
        self::assertTrue($barcode->isValid('Test93+'));

        // @TODO: CODE93 EXTENDED CHECKSUM VALIDATION MISSING
        // $barcode->useChecksum(true);
        // self::assertTrue($barcode->isValid('CODE 93E0'));
        // self::assertFalse($barcode->isValid('CODE 93E1'));
    }

    public function testEAN2(): void
    {
        $barcode = new Barcode([
            'adapter'     => Barcode\Ean2::class,
            'useChecksum' => false,
        ]);

        self::assertTrue($barcode->isValid('12'));
        self::assertFalse($barcode->isValid('1'));
        self::assertFalse($barcode->isValid('123'));
    }

    public function testEAN5(): void
    {
        $barcode = new Barcode([
            'adapter'     => Barcode\Ean5::class,
            'useChecksum' => true,
        ]);

        self::assertTrue($barcode->isValid('12345'));
        self::assertFalse($barcode->isValid('1234'));
        self::assertFalse($barcode->isValid('123456'));
    }

    public function testEAN8(): void
    {
        $barcode = new Barcode([
            'adapter'     => Barcode\Ean8::class,
            'useChecksum' => true,
        ]);

        self::assertTrue($barcode->isValid('12345670'));
        self::assertFalse($barcode->isValid('123'));
        self::assertFalse($barcode->isValid('12345671'));
        self::assertTrue($barcode->isValid('1234567'));
    }

    public function testEAN12(): void
    {
        $barcode = new Barcode([
            'adapter'     => Barcode\Ean12::class,
            'useChecksum' => true,
        ]);

        self::assertTrue($barcode->isValid('123456789012'));
        self::assertFalse($barcode->isValid('123'));
        self::assertFalse($barcode->isValid('123456789013'));
    }

    public function testEAN13(): void
    {
        $barcode = new Barcode([
            'adapter'     => Barcode\Ean13::class,
            'useChecksum' => true,
        ]);

        self::assertTrue($barcode->isValid('1234567890128'));
        self::assertFalse($barcode->isValid('123'));
        self::assertFalse($barcode->isValid('1234567890127'));
    }

    public function testEAN14(): void
    {
        $barcode = new Barcode([
            'adapter'     => Barcode\Ean14::class,
            'useChecksum' => true,
        ]);

        self::assertTrue($barcode->isValid('12345678901231'));
        self::assertFalse($barcode->isValid('123'));
        self::assertFalse($barcode->isValid('12345678901232'));
    }

    public function testEAN18(): void
    {
        $barcode = new Barcode([
            'adapter'     => Barcode\Ean18::class,
            'useChecksum' => true,
        ]);

        self::assertTrue($barcode->isValid('123456789012345675'));
        self::assertFalse($barcode->isValid('123'));
        self::assertFalse($barcode->isValid('123456789012345676'));
    }

    public function testGTIN12(): void
    {
        $barcode = new Barcode([
            'adapter'     => Barcode\Gtin12::class,
            'useChecksum' => true,
        ]);

        self::assertTrue($barcode->isValid('123456789012'));
        self::assertFalse($barcode->isValid('123'));
        self::assertFalse($barcode->isValid('123456789013'));
    }

    public function testGTIN13(): void
    {
        $barcode = new Barcode([
            'adapter'     => Barcode\Gtin13::class,
            'useChecksum' => true,
        ]);

        self::assertTrue($barcode->isValid('1234567890128'));
        self::assertFalse($barcode->isValid('123'));
        self::assertFalse($barcode->isValid('1234567890127'));
    }

    public function testGTIN14(): void
    {
        $barcode = new Barcode([
            'adapter'     => Barcode\Gtin14::class,
            'useChecksum' => true,
        ]);

        self::assertTrue($barcode->isValid('12345678901231'));
        self::assertFalse($barcode->isValid('123'));
        self::assertFalse($barcode->isValid('12345678901232'));
    }

    public function testIDENTCODE(): void
    {
        $barcode = new Barcode([
            'adapter'     => Barcode\Identcode::class,
            'useChecksum' => true,
        ]);

        self::assertTrue($barcode->isValid('564000000050'));
        self::assertFalse($barcode->isValid('123'));
        self::assertFalse($barcode->isValid('0563102430313'));
        self::assertFalse($barcode->isValid('564000000051'));
    }

    public function testINTELLIGENTMAIL(): void
    {
        $barcode = new Barcode([
            'adapter'     => Barcode\Intelligentmail::class,
            'useChecksum' => true,
        ]);

        self::assertTrue($barcode->isValid('01234567094987654321'));
        self::assertFalse($barcode->isValid('123'));
        self::assertFalse($barcode->isValid('5555512371'));
    }

    public function testISSN(): void
    {
        $barcode = new Barcode([
            'adapter'     => Barcode\Issn::class,
            'useChecksum' => true,
        ]);

        self::assertTrue($barcode->isValid('1144875X'));
        self::assertFalse($barcode->isValid('123'));
        self::assertFalse($barcode->isValid('1144874X'));

        self::assertTrue($barcode->isValid('9771144875007'));
        self::assertFalse($barcode->isValid('97711448750X7'));
    }

    public function testITF14(): void
    {
        $barcode = new Barcode([
            'adapter'     => Barcode\Itf14::class,
            'useChecksum' => true,
        ]);

        self::assertTrue($barcode->isValid('00075678164125'));
        self::assertFalse($barcode->isValid('123'));
        self::assertFalse($barcode->isValid('00075678164124'));
    }

    public function testLEITCODE(): void
    {
        $barcode = new Barcode([
            'adapter'     => Barcode\Leitcode::class,
            'useChecksum' => true,
        ]);

        self::assertTrue($barcode->isValid('21348075016401'));
        self::assertFalse($barcode->isValid('123'));
        self::assertFalse($barcode->isValid('021348075016401'));
        self::assertFalse($barcode->isValid('21348075016402'));
    }

    public function testPLANET(): void
    {
        $barcode = new Barcode([
            'adapter'     => Barcode\Planet::class,
            'useChecksum' => true,
        ]);

        self::assertTrue($barcode->isValid('401234567891'));
        self::assertFalse($barcode->isValid('123'));
        self::assertFalse($barcode->isValid('401234567892'));
    }

    public function testPOSTNET(): void
    {
        $barcode = new Barcode([
            'adapter'     => Barcode\Postnet::class,
            'useChecksum' => true,
        ]);

        self::assertTrue($barcode->isValid('5555512372'));
        self::assertFalse($barcode->isValid('123'));
        self::assertFalse($barcode->isValid('5555512371'));
    }

    public function testROYALMAIL(): void
    {
        $barcode = new Barcode([
            'adapter'     => Barcode\Royalmail::class,
            'useChecksum' => true,
        ]);

        self::assertTrue($barcode->isValid('SN34RD1AK'));
        self::assertFalse($barcode->isValid('123'));
        self::assertFalse($barcode->isValid('SN34RD1AW'));

        self::assertTrue($barcode->isValid('012345W'));
        self::assertTrue($barcode->isValid('06CIOUH'));
    }

    public function testSSCC(): void
    {
        $barcode = new Barcode([
            'adapter'     => Barcode\Sscc::class,
            'useChecksum' => true,
        ]);

        self::assertTrue($barcode->isValid('123456789012345675'));
        self::assertFalse($barcode->isValid('123'));
        self::assertFalse($barcode->isValid('123456789012345676'));
    }

    public function testUPCA(): void
    {
        $barcode = new Barcode([
            'adapter'     => Barcode\Upca::class,
            'useChecksum' => true,
        ]);

        self::assertTrue($barcode->isValid('123456789012'));
        self::assertFalse($barcode->isValid('123'));
        self::assertFalse($barcode->isValid('123456789013'));
    }

    public function testUPCE(): void
    {
        $barcode = new Barcode([
            'adapter'     => Barcode\Upce::class,
            'useChecksum' => true,
        ]);

        self::assertTrue($barcode->isValid('02345673'));
        self::assertFalse($barcode->isValid('02345672'));
        self::assertFalse($barcode->isValid('123'));
        self::assertTrue($barcode->isValid('123456'));
        self::assertTrue($barcode->isValid('0234567'));
    }

    #[Group('Laminas-10116')]
    public function testArrayLengthMessage(): void
    {
        $barcode = new Barcode([
            'adapter'     => Barcode\Ean8::class,
            'useChecksum' => true,
        ]);

        self::assertFalse($barcode->isValid('123'));

        $message = $barcode->getMessages();

        self::assertArrayHasKey('barcodeInvalidLength', $message);
        self::assertStringContainsString('length of 7/8 characters', $message['barcodeInvalidLength']);
    }

    #[Group('Laminas-8673')]
    public function testCODABAR(): void
    {
        $barcode = new Barcode([
            'adapter'     => Barcode\Codabar::class,
            'useChecksum' => true,
        ]);

        self::assertTrue($barcode->isValid('123456789'));
        self::assertTrue($barcode->isValid('A123A'));
        self::assertTrue($barcode->isValid('A123C'));
        self::assertFalse($barcode->isValid('A123E'));
        self::assertFalse($barcode->isValid('A1A23C'));
        self::assertTrue($barcode->isValid('T123*'));
        self::assertFalse($barcode->isValid('*123A'));
    }

    #[Group('Laminas-11532')]
    public function testIssnWithMod0(): void
    {
        $barcode = new Barcode([
            'adapter'     => Barcode\Issn::class,
            'useChecksum' => true,
        ]);

        self::assertTrue($barcode->isValid('18710360'));
    }

    #[Group('Laminas-8674')]
    public function testCODE128(): void
    {
        $barcode = new Barcode([
            'adapter'     => Barcode\Code128::class,
            'useChecksum' => true,
        ]);

        self::assertTrue($barcode->isValid('ˆCODE128:Š'));
        self::assertTrue($barcode->isValid('‡01231[Š'));

        $barcode = new Barcode([
            'adapter'     => Barcode\Code128::class,
            'useChecksum' => false,
        ]);

        self::assertTrue($barcode->isValid('012345'));
        self::assertTrue($barcode->isValid('ABCDEF'));
        self::assertFalse($barcode->isValid('01234Ê'));
    }

    /**
     * Test if EAN-13 contains only numeric characters
     */
    #[Group('Laminas-3297')]
    public function testEan13ContainsOnlyNumeric(): void
    {
        $barcode = new Barcode([
            'adapter'     => Barcode\Ean13::class,
            'useChecksum' => true,
        ]);

        self::assertFalse($barcode->isValid('3RH1131-1BB40'));
    }
}
