<?php

/**
 * @see       https://github.com/laminas/laminas-validator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-validator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-validator/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Validator;

use PHPUnit\Framework\TestCase;
use Laminas\Validator\BusinessIdentifierCode;

class BusinessIdentifierCodeTest extends TestCase
{
    public function successProvider() : array
    {
        return [
            // UPPERCASE
            'BANQUE ATLANTIQUE COTE D\'IVOIRE, ABIDJAN, Cote d\'Ivoire' => ['ATCICIAB'],
            'BANQUE NATIONALE DU CANADA, MONTREAL, Canada' => ['BNDCCAMMINT'],
            'BDO UNIBANK, INC., MANILA, Philippines' => ['BNORPHMM'],
            'FEDERATION DES CAISSES DESJARDINS DU QUEBEC, LEVIS, Canada' => ['CCDQCAMM'],
            'COMMERZBANK AG, FRANKFURT AM MAIN, Germany' => ['COBADEFF'],
            'DANSKE BANK A/S, COPENHAGEN, Denmark' => ['DABADKKK'],
            'DEUTSCHE BANK AG, FRANKFURT AM MAIN, Germany' => ['DEUTDEFF'],
            'DB PRIVAT-UND FIRMENKUNDENBANK, DUSSELDORF, Germany' => ['DEUTDEDBDUE'],
            'DAH SING BANK (CHINA) LIMITED, SHANGHAI, China' => ['DSBACNBXSHA'],
            'FINANCIERE DES PAIEMENTS ELECTRONIQUES, CHARENTON LE PONT, France' => ['FPELFR21XXX'],
            'BNP PARIBAS FORTIS, BRUSSELS, Belgium' => ['GEBABEBB'],
            'PROCREDIT BANK SH.A KOSOVO, HEAD QUARTER, PRISTINA, Kosovo' => ['MBKOXKPRXXX'],
            'NEDBANK LIMITED, JOHANNESBURG, South Africa' => ['NEDSZAJJ'],
            'LA BANQUE POSTALE, MONTPELLIER, France' => ['PSSTFRPPMON'],

            // lowercase
            'DEUTSCHE BANK AG, BAD HOMBURG, Germany' => ['deutdeff500'],
            'NEDBANK LIMITED, JOHANNESBURG, South Africa (primary office)' => ['nedszajjxxx'],
            'LA BANQUE POSTALE, NANTES, France' => ['psstfrppnte'],
            'UNICREDIT S.P.A., MILANO, Italy' => ['uncritmm'],
        ];
    }

    /**
     * @dataProvider successProvider
     *
     * @return void
     */
    public function testValidateSuccess(string $code): void
    {
        $validator = new BusinessIdentifierCode();
        self::assertTrue($validator->isValid($code));
    }

    public function notAStringProvider() : array
    {
        return [
            'number' => [123],
            'array'  => [['DEUTDEFF']],
            'object' => [new \stdClass()],
        ];
    }

    /**
     * @dataProvider notAStringProvider
     *
     * @return void
     */
    public function testNotAStringFailure($code): void
    {
        $validator = new BusinessIdentifierCode();
        self::assertFalse($validator->isValid($code));
        self::assertCount(1, $validator->getMessages());
        self::assertSame(
            'Invalid type given; string expected',
            $validator->getMessages()[BusinessIdentifierCode::NOT_STRING]
        );
    }

    public function notBicFormatProvider() : array
    {
        return [
            'too short' => ['SHORT'],
            'too long' => ['SOLOOOOOOOOOOOOOOOOOOONG'],
            'use numbers for country code' => ['DEUT12AA'],
        ];
    }

    /**
     * @dataProvider notBicFormatProvider
     *
     * @return void
     */
    public function testNotBicFormatFailure(string $code): void
    {
        $validator = new BusinessIdentifierCode();
        self::assertFalse($validator->isValid($code));
        self::assertCount(1, $validator->getMessages());
        self::assertSame('Invalid BIC format', $validator->getMessages()[BusinessIdentifierCode::INVALID]);
    }

    public function notSwiftCountryCodeProvider() : array
    {
        return [
            'AA is not a assigned code' => ['DEUTAAFF'],
            'UK is not the iso code for the united kingdom' => ['ABCDUKFF'],
        ];
    }

    /**
     * @dataProvider notSwiftCountryCodeProvider
     *
     * @return void
     */
    public function testNotSwiftCountryCodeFailure(string $code): void
    {
        $validator = new BusinessIdentifierCode();
        self::assertFalse($validator->isValid($code));
        self::assertCount(1, $validator->getMessages());
        self::assertSame('Invalid country code', $validator->getMessages()[BusinessIdentifierCode::NOT_VALID_COUNTRY]);
    }
}
