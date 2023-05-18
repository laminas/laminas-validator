<?php

declare(strict_types=1);

namespace LaminasTest\Validator;

use Laminas\Validator\BusinessIdentifierCode;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use stdClass;

final class BusinessIdentifierCodeTest extends TestCase
{
    /** @psalm-return array<string, array{0: string}> */
    public static function successProvider(): array
    {
        return [
            // UPPERCASE
            'BANQUE ATLANTIQUE COTE D\'IVOIRE, ABIDJAN, Cote d\'Ivoire'         => ['ATCICIAB'],
            'BANQUE NATIONALE DU CANADA, MONTREAL, Canada'                      => ['BNDCCAMMINT'],
            'BDO UNIBANK, INC., MANILA, Philippines'                            => ['BNORPHMM'],
            'FEDERATION DES CAISSES DESJARDINS DU QUEBEC, LEVIS, Canada'        => ['CCDQCAMM'],
            'COMMERZBANK AG, FRANKFURT AM MAIN, Germany'                        => ['COBADEFF'],
            'DANSKE BANK A/S, COPENHAGEN, Denmark'                              => ['DABADKKK'],
            'DEUTSCHE BANK AG, FRANKFURT AM MAIN, Germany'                      => ['DEUTDEFF'],
            'DB PRIVAT-UND FIRMENKUNDENBANK, DUSSELDORF, Germany'               => ['DEUTDEDBDUE'],
            'DAH SING BANK (CHINA) LIMITED, SHANGHAI, China'                    => ['DSBACNBXSHA'],
            'FINANCIERE DES PAIEMENTS ELECTRONIQUES, CHARENTON LE PONT, France' => ['FPELFR21XXX'],
            'BNP PARIBAS FORTIS, BRUSSELS, Belgium'                             => ['GEBABEBB'],
            'PROCREDIT BANK SH.A KOSOVO, HEAD QUARTER, PRISTINA, Kosovo'        => ['MBKOXKPRXXX'],
            'NEDBANK LIMITED, JOHANNESBURG, South Africa'                       => ['NEDSZAJJ'],
            'LA BANQUE POSTALE, MONTPELLIER, France'                            => ['PSSTFRPPMON'],

            // lowercase
            'DEUTSCHE BANK AG, BAD HOMBURG, Germany'                       => ['deutdeff500'],
            'NEDBANK LIMITED, JOHANNESBURG, South Africa (primary office)' => ['nedszajjxxx'],
            'LA BANQUE POSTALE, NANTES, France'                            => ['psstfrppnte'],
            'UNICREDIT S.P.A., MILANO, Italy'                              => ['uncritmm'],
        ];
    }

    #[DataProvider('successProvider')]
    public function testValidateSuccess(string $code): void
    {
        $validator = new BusinessIdentifierCode();

        self::assertTrue($validator->isValid($code));
    }

    /** @psalm-return array<string, array{0: mixed}> */
    public static function notAStringProvider(): array
    {
        return [
            'number' => [123],
            'array'  => [['DEUTDEFF']],
            'object' => [new stdClass()],
        ];
    }

    #[DataProvider('notAStringProvider')]
    public function testNotAStringFailure(mixed $code): void
    {
        $validator = new BusinessIdentifierCode();

        self::assertFalse($validator->isValid($code));
        self::assertCount(1, $validator->getMessages());
        self::assertSame(
            'Invalid type given; string expected',
            $validator->getMessages()[BusinessIdentifierCode::NOT_STRING]
        );
    }

    /** @psalm-return array<string, array{0: string}> */
    public static function notBicFormatProvider(): array
    {
        return [
            'too short'                    => ['SHORT'],
            'too long'                     => ['SOLOOOOOOOOOOOOOOOOOOONG'],
            'use numbers for country code' => ['DEUT12AA'],
        ];
    }

    #[DataProvider('notBicFormatProvider')]
    public function testNotBicFormatFailure(string $code): void
    {
        $validator = new BusinessIdentifierCode();

        self::assertFalse($validator->isValid($code));
        self::assertCount(1, $validator->getMessages());
        self::assertSame('Invalid BIC format', $validator->getMessages()[BusinessIdentifierCode::INVALID]);
    }

    /** @psalm-return array<string, array{0: string}> */
    public static function notSwiftCountryCodeProvider(): array
    {
        return [
            'AA is not a assigned code'                     => ['DEUTAAFF'],
            'UK is not the iso code for the united kingdom' => ['ABCDUKFF'],
        ];
    }

    #[DataProvider('notSwiftCountryCodeProvider')]
    public function testNotSwiftCountryCodeFailure(string $code): void
    {
        $validator = new BusinessIdentifierCode();

        self::assertFalse($validator->isValid($code));
        self::assertCount(1, $validator->getMessages());
        self::assertSame('Invalid country code', $validator->getMessages()[BusinessIdentifierCode::NOT_VALID_COUNTRY]);
    }
}
