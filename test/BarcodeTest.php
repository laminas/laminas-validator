<?php

declare(strict_types=1);

namespace LaminasTest\Validator;

use ArrayObject;
use Laminas\Validator\Barcode;
use Laminas\Validator\Barcode\AdapterInterface;
use Laminas\Validator\Barcode\Ean13;
use Laminas\Validator\Exception\InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

use function array_keys;
use function extension_loaded;

final class BarcodeTest extends TestCase
{
    /**
     * @psalm-return array<string, array{0: null|array, 1: class-string}>
     */
    public static function provideBarcodeConstructor(): array
    {
        return [
            'null'        => [null, Barcode\Ean13::class],
            'empty-array' => [[], Barcode\Ean13::class],
        ];
    }

    /**
     * @param array<string, mixed>|null $options
     * @param class-string $expectedInstance
     */
    #[DataProvider('provideBarcodeConstructor')]
    public function testBarcodeConstructor(?array $options, string $expectedInstance): void
    {
        $barcode = new Barcode($options);

        self::assertInstanceOf($expectedInstance, $barcode->getAdapter());
    }

    public function testNoneExisting(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('not found');

        new Barcode('\Laminas\Validate\BarcodeTest\NonExistentClassName');
    }

    public function testSetAdapter(): void
    {
        $barcode = new Barcode('upca');
        self::assertTrue($barcode->isValid('065100004327'));

        $barcode->setAdapter('ean13');
        self::assertTrue($barcode->isValid('0075678164125'));
    }

    public function testSetCustomAdapter(): void
    {
        $barcode = new Barcode([
            'adapter' => $this->createMock(AdapterInterface::class),
        ]);

        self::assertInstanceOf(AdapterInterface::class, $barcode->getAdapter());
    }

    /**
     * @Laminas-4352
     */
    public function testNonStringValidation(): void
    {
        $barcode = new Barcode('upca');

        self::assertFalse($barcode->isValid(106510000.4327));
        self::assertFalse($barcode->isValid(['065100004327']));

        $barcode = new Barcode('ean13');

        self::assertFalse($barcode->isValid(06510000.4327));
        self::assertFalse($barcode->isValid(['065100004327']));
    }

    public function testInvalidChecksumAdapter(): void
    {
        require_once __DIR__ . '/_files/MyBarcode1.php';
        $barcode = new Barcode('MyBarcode1');

        self::assertFalse($barcode->isValid('0000000'));
        self::assertArrayHasKey('barcodeFailed', $barcode->getMessages());
        self::assertFalse($barcode->getAdapter()->hasValidChecksum('0000000'));
    }

    public function testInvalidCharAdapter(): void
    {
        require_once __DIR__ . '/_files/MyBarcode1.php';
        $barcode = new Barcode('MyBarcode1');

        self::assertFalse($barcode->getAdapter()->hasValidCharacters(123));
    }

    public function testAscii128CharacterAdapter(): void
    {
        require_once __DIR__ . '/_files/MyBarcode2.php';
        $barcode = new Barcode('MyBarcode2');

        self::assertTrue($barcode->getAdapter()->hasValidCharacters('1234QW!"'));
    }

    public function testInvalidLengthAdapter(): void
    {
        require_once __DIR__ . '/_files/MyBarcode2.php';
        $barcode = new Barcode('MyBarcode2');

        self::assertFalse($barcode->getAdapter()->hasValidLength(123));
    }

    public function testArrayLengthAdapter(): void
    {
        require_once __DIR__ . '/_files/MyBarcode2.php';
        $barcode = new Barcode('MyBarcode2');

        self::assertTrue($barcode->getAdapter()->hasValidLength('1'));
        self::assertFalse($barcode->getAdapter()->hasValidLength('12'));
        self::assertTrue($barcode->getAdapter()->hasValidLength('123'));
        self::assertFalse($barcode->getAdapter()->hasValidLength('1234'));
    }

    public function testArrayLengthAdapter2(): void
    {
        require_once __DIR__ . '/_files/MyBarcode3.php';
        $barcode = new Barcode('MyBarcode3');

        self::assertTrue($barcode->getAdapter()->hasValidLength('1'));
        self::assertTrue($barcode->getAdapter()->hasValidLength('12'));
        self::assertTrue($barcode->getAdapter()->hasValidLength('123'));
        self::assertTrue($barcode->getAdapter()->hasValidLength('1234'));
    }

    public function testOddLengthAdapter(): void
    {
        require_once __DIR__ . '/_files/MyBarcode4.php';
        $barcode = new Barcode('MyBarcode4');

        self::assertTrue($barcode->getAdapter()->hasValidLength('1'));
        self::assertFalse($barcode->getAdapter()->hasValidLength('12'));
        self::assertTrue($barcode->getAdapter()->hasValidLength('123'));
        self::assertFalse($barcode->getAdapter()->hasValidLength('1234'));
    }

    public function testInvalidAdapter(): void
    {
        $barcode = new Barcode('Ean13');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('does not implement');

        require_once __DIR__ . '/_files/MyBarcode5.php';
        $barcode->setAdapter('MyBarcode5');
    }

    public function testArrayConstructAdapter(): void
    {
        $barcode = new Barcode(['adapter' => 'Ean13', 'options' => 'unknown', 'useChecksum' => false]);

        self::assertInstanceOf(Ean13::class, $barcode->getAdapter());
        self::assertFalse($barcode->useChecksum());
    }

    public function testDefaultArrayConstructWithMissingAdapter(): void
    {
        $barcode = new Barcode(['options' => 'unknown', 'checksum' => false]);

        self::assertTrue($barcode->isValid('0075678164125'));
    }

    public function testTraversableConstructAdapter(): void
    {
        $barcode = new Barcode(new ArrayObject(['adapter' => 'Ean13', 'options' => 'unknown', 'useChecksum' => false]));

        self::assertTrue($barcode->isValid('0075678164125'));
    }

    public function testRoyalmailIsValid(): void
    {
        $barcode = new Barcode(['adapter' => 'Royalmail', 'useChecksum' => true]);

        self::assertTrue($barcode->isValid('1234562'));
    }

    public function testCODE25(): void
    {
        $barcode = new Barcode('code25');

        self::assertTrue($barcode->isValid('0123456789101213'));
        self::assertTrue($barcode->isValid('123'));
        self::assertFalse($barcode->isValid('123a'));

        $barcode->useChecksum(true);

        self::assertTrue($barcode->isValid('0123456789101214'));
        self::assertFalse($barcode->isValid('0123456789101213'));
    }

    public function testCODE25INTERLEAVED(): void
    {
        $barcode = new Barcode('code25interleaved');

        self::assertTrue($barcode->isValid('0123456789101213'));
        self::assertFalse($barcode->isValid('123'));

        $barcode->useChecksum(true);

        self::assertTrue($barcode->isValid('0123456789101214'));
        self::assertFalse($barcode->isValid('0123456789101213'));
    }

    public function testCODE39(): void
    {
        $barcode = new Barcode('code39');

        self::assertTrue($barcode->isValid('TEST93TEST93TEST93TEST93Y+'));
        self::assertTrue($barcode->isValid('00075678164124'));
        self::assertFalse($barcode->isValid('Test93Test93Test'));

        $barcode->useChecksum(true);

        self::assertTrue($barcode->isValid('159AZH'));
        self::assertFalse($barcode->isValid('159AZG'));
    }

    public function testCODE39EXT(): void
    {
        $barcode = new Barcode('code39ext');

        self::assertTrue($barcode->isValid('TEST93TEST93TEST93TEST93Y+'));
        self::assertTrue($barcode->isValid('00075678164124'));
        self::assertTrue($barcode->isValid('Test93Test93Test'));

// @TODO: CODE39 EXTENDED CHECKSUM VALIDATION MISSING
//        $barcode->useChecksum(true);
//        self::assertTrue($barcode->isValid('159AZH'));
//        self::assertFalse($barcode->isValid('159AZG'));
    }

    public function testCODE93(): void
    {
        $barcode = new Barcode('code93');

        self::assertTrue($barcode->isValid('TEST93+'));
        self::assertFalse($barcode->isValid('Test93+'));

        $barcode->useChecksum(true);

        self::assertTrue($barcode->isValid('CODE 93E0'));
        self::assertFalse($barcode->isValid('CODE 93E1'));
    }

    public function testCODE93EXT(): void
    {
        $barcode = new Barcode('code93ext');

        self::assertTrue($barcode->isValid('TEST93+'));
        self::assertTrue($barcode->isValid('Test93+'));

// @TODO: CODE93 EXTENDED CHECKSUM VALIDATION MISSING
//        $barcode->useChecksum(true);
//        self::assertTrue($barcode->isValid('CODE 93E0'));
//        self::assertFalse($barcode->isValid('CODE 93E1'));
    }

    public function testEAN2(): void
    {
        $barcode = new Barcode('ean2');

        self::assertTrue($barcode->isValid('12'));
        self::assertFalse($barcode->isValid('1'));
        self::assertFalse($barcode->isValid('123'));
    }

    public function testEAN5(): void
    {
        $barcode = new Barcode('ean5');

        self::assertTrue($barcode->isValid('12345'));
        self::assertFalse($barcode->isValid('1234'));
        self::assertFalse($barcode->isValid('123456'));
    }

    public function testEAN8(): void
    {
        $barcode = new Barcode('ean8');

        self::assertTrue($barcode->isValid('12345670'));
        self::assertFalse($barcode->isValid('123'));
        self::assertFalse($barcode->isValid('12345671'));
        self::assertTrue($barcode->isValid('1234567'));
    }

    public function testEAN12(): void
    {
        $barcode = new Barcode('ean12');

        self::assertTrue($barcode->isValid('123456789012'));
        self::assertFalse($barcode->isValid('123'));
        self::assertFalse($barcode->isValid('123456789013'));
    }

    public function testEAN13(): void
    {
        $barcode = new Barcode('ean13');

        self::assertTrue($barcode->isValid('1234567890128'));
        self::assertFalse($barcode->isValid('123'));
        self::assertFalse($barcode->isValid('1234567890127'));
    }

    public function testEAN14(): void
    {
        $barcode = new Barcode('ean14');

        self::assertTrue($barcode->isValid('12345678901231'));
        self::assertFalse($barcode->isValid('123'));
        self::assertFalse($barcode->isValid('12345678901232'));
    }

    public function testEAN18(): void
    {
        $barcode = new Barcode('ean18');

        self::assertTrue($barcode->isValid('123456789012345675'));
        self::assertFalse($barcode->isValid('123'));
        self::assertFalse($barcode->isValid('123456789012345676'));
    }

    public function testGTIN12(): void
    {
        $barcode = new Barcode('gtin12');

        self::assertTrue($barcode->isValid('123456789012'));
        self::assertFalse($barcode->isValid('123'));
        self::assertFalse($barcode->isValid('123456789013'));
    }

    public function testGTIN13(): void
    {
        $barcode = new Barcode('gtin13');

        self::assertTrue($barcode->isValid('1234567890128'));
        self::assertFalse($barcode->isValid('123'));
        self::assertFalse($barcode->isValid('1234567890127'));
    }

    public function testGTIN14(): void
    {
        $barcode = new Barcode('gtin14');

        self::assertTrue($barcode->isValid('12345678901231'));
        self::assertFalse($barcode->isValid('123'));
        self::assertFalse($barcode->isValid('12345678901232'));
    }

    public function testIDENTCODE(): void
    {
        $barcode = new Barcode('identcode');

        self::assertTrue($barcode->isValid('564000000050'));
        self::assertFalse($barcode->isValid('123'));
        self::assertFalse($barcode->isValid('0563102430313'));
        self::assertFalse($barcode->isValid('564000000051'));
    }

    public function testINTELLIGENTMAIL(): void
    {
        $barcode = new Barcode('intelligentmail');

        self::assertTrue($barcode->isValid('01234567094987654321'));
        self::assertFalse($barcode->isValid('123'));
        self::assertFalse($barcode->isValid('5555512371'));
    }

    public function testISSN(): void
    {
        $barcode = new Barcode('issn');

        self::assertTrue($barcode->isValid('1144875X'));
        self::assertFalse($barcode->isValid('123'));
        self::assertFalse($barcode->isValid('1144874X'));

        self::assertTrue($barcode->isValid('9771144875007'));
        self::assertFalse($barcode->isValid('97711448750X7'));
    }

    public function testITF14(): void
    {
        $barcode = new Barcode('itf14');

        self::assertTrue($barcode->isValid('00075678164125'));
        self::assertFalse($barcode->isValid('123'));
        self::assertFalse($barcode->isValid('00075678164124'));
    }

    public function testLEITCODE(): void
    {
        $barcode = new Barcode('leitcode');

        self::assertTrue($barcode->isValid('21348075016401'));
        self::assertFalse($barcode->isValid('123'));
        self::assertFalse($barcode->isValid('021348075016401'));
        self::assertFalse($barcode->isValid('21348075016402'));
    }

    public function testPLANET(): void
    {
        $barcode = new Barcode('planet');

        self::assertTrue($barcode->isValid('401234567891'));
        self::assertFalse($barcode->isValid('123'));
        self::assertFalse($barcode->isValid('401234567892'));
    }

    public function testPOSTNET(): void
    {
        $barcode = new Barcode('postnet');

        self::assertTrue($barcode->isValid('5555512372'));
        self::assertFalse($barcode->isValid('123'));
        self::assertFalse($barcode->isValid('5555512371'));
    }

    public function testROYALMAIL(): void
    {
        $barcode = new Barcode('royalmail');

        self::assertTrue($barcode->isValid('SN34RD1AK'));
        self::assertFalse($barcode->isValid('123'));
        self::assertFalse($barcode->isValid('SN34RD1AW'));

        self::assertTrue($barcode->isValid('012345W'));
        self::assertTrue($barcode->isValid('06CIOUH'));
    }

    public function testSSCC(): void
    {
        $barcode = new Barcode('sscc');

        self::assertTrue($barcode->isValid('123456789012345675'));
        self::assertFalse($barcode->isValid('123'));
        self::assertFalse($barcode->isValid('123456789012345676'));
    }

    public function testUPCA(): void
    {
        $barcode = new Barcode('upca');

        self::assertTrue($barcode->isValid('123456789012'));
        self::assertFalse($barcode->isValid('123'));
        self::assertFalse($barcode->isValid('123456789013'));
    }

    public function testUPCE(): void
    {
        $barcode = new Barcode('upce');

        self::assertTrue($barcode->isValid('02345673'));
        self::assertFalse($barcode->isValid('02345672'));
        self::assertFalse($barcode->isValid('123'));
        self::assertTrue($barcode->isValid('123456'));
        self::assertTrue($barcode->isValid('0234567'));
    }

    #[Group('Laminas-10116')]
    public function testArrayLengthMessage(): void
    {
        $barcode = new Barcode('ean8');

        self::assertFalse($barcode->isValid('123'));

        $message = $barcode->getMessages();

        self::assertArrayHasKey('barcodeInvalidLength', $message);
        self::assertStringContainsString('length of 7/8 characters', $message['barcodeInvalidLength']);
    }

    #[Group('Laminas-8673')]
    public function testCODABAR(): void
    {
        $barcode = new Barcode('codabar');

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
        $barcode = new Barcode('issn');

        self::assertTrue($barcode->isValid('18710360'));
    }

    #[Group('Laminas-8674')]
    public function testCODE128(): void
    {
        if (! extension_loaded('iconv')) {
            self::markTestSkipped('Missing ext/iconv');
        }

        $barcode = new Barcode('code128');

        self::assertTrue($barcode->isValid('ˆCODE128:Š'));
        self::assertTrue($barcode->isValid('‡01231[Š'));

        $barcode->useChecksum(false);

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
        $barcode = new Barcode('ean13');

        self::assertFalse($barcode->isValid('3RH1131-1BB40'));
    }

    public function testEqualsMessageTemplates(): void
    {
        $validator = new Barcode('code25');

        self::assertSame(
            [
                Barcode::FAILED,
                Barcode::INVALID_CHARS,
                Barcode::INVALID_LENGTH,
                Barcode::INVALID,
            ],
            array_keys($validator->getMessageTemplates())
        );
        self::assertSame($validator->getOption('messageTemplates'), $validator->getMessageTemplates());
    }

    public function testEqualsMessageVariables(): void
    {
        $validator        = new Barcode('code25');
        $messageVariables = [
            'length' => ['options' => 'length'],
        ];

        self::assertSame($messageVariables, $validator->getOption('messageVariables'));
        self::assertSame(array_keys($messageVariables), $validator->getMessageVariables());
    }
}
