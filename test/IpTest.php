<?php

declare(strict_types=1);

namespace LaminasTest\Validator;

use Laminas\Validator\Exception\InvalidArgumentException;
use Laminas\Validator\Ip;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

final class IpTest extends TestCase
{
    /**
     * Ensures that the validator follows expected behavior
     */
    public function testBasic(): void
    {
        $validator = new Ip();
        self::assertTrue($validator->isValid('1.2.3.4'));
        self::assertTrue($validator->isValid('10.0.0.1'));
        self::assertTrue($validator->isValid('255.255.255.255'));

        self::assertFalse($validator->isValid('0.0.0.256'));
        self::assertFalse($validator->isValid('1.2.3.4.5'));
    }

    public function testZeroIpForLaminas420(): void
    {
        $validator = new Ip();
        self::assertTrue($validator->isValid('0.0.0.0'));
    }

    public function testOnlyIpv4(): void
    {
        $validator = new Ip([
            'allowipv4'      => true,
            'allowipv6'      => false,
            'allowipvfuture' => false,
            'allowliteral'   => false,
        ]);

        self::assertTrue($validator->isValid('1.2.3.4'));
        self::assertFalse($validator->isValid('a:b:c:d:e::1.2.3.4'));
        self::assertFalse($validator->isValid('v1.09azAZ-._~!$&\'()*+,;='));
    }

    public function testOnlyIpv6(): void
    {
        $validator = new Ip([
            'allowipv4'      => false,
            'allowipv6'      => true,
            'allowipvfuture' => false,
            'allowliteral'   => false,
        ]);

        self::assertFalse($validator->isValid('1.2.3.4'));
        self::assertTrue($validator->isValid('a:b:c:d:e::1.2.3.4'));
        self::assertFalse($validator->isValid('v1.09azAZ-._~!$&\'()*+,;='));
    }

    public function testOnlyIpvfuture(): void
    {
        $validator = new Ip([
            'allowipv4'      => false,
            'allowipv6'      => false,
            'allowipvfuture' => true,
            'allowliteral'   => false,
        ]);

        self::assertFalse($validator->isValid('1.2.3.4'));
        self::assertFalse($validator->isValid('a:b:c:d:e::1.2.3.4'));
        self::assertTrue($validator->isValid("v1.09azAZ-._~!$&'()*+,;=:"));
    }

    public function testLiteral(): void
    {
        $validator = new Ip([
            'allowipv4'      => true,
            'allowipv6'      => true,
            'allowipvfuture' => true,
            'allowliteral'   => true,
        ]);

        self::assertFalse($validator->isValid('[1.2.3.4]'));
        self::assertTrue($validator->isValid('[a:b:c:d:e::1.2.3.4]'));
        self::assertFalse($validator->isValid('[[a:b:c:d:e::1.2.3.4]]'));
        self::assertFalse($validator->isValid('[[a:b:c:d:e::1.2.3.4]'));
        self::assertFalse($validator->isValid('[[a:b:c:d:e::1.2.3.4'));
        self::assertFalse($validator->isValid('[a:b:c:d:e::1.2.3.4]]'));
        self::assertFalse($validator->isValid('a:b:c:d:e::1.2.3.4]]'));
        self::assertTrue($validator->isValid('[v1.ZZ:ZZ]'));
    }

    /**
     * @psalm-return array<string, array{
     *     0: string,
     *     1: bool
     * }>
     */
    public static function ipvFutureProvider(): array
    {
        return [
            'IPvFuture: Version 1 disallowed'  => ['v1.A', true],
            'IPvFuture: Version D disallowed'  => ['vD.A', true],
            'IPvFuture: Version 46 disallowed' => ['v46.A', true],
            'IPvFuture: Version 4 allowed'     => ['v4.A', false],
            'IPvFuture: Version 6 allowed'     => ['v6.A', false],
        ];
    }

    /**
     * Versions 4 and 6 are not allowed in IPvFuture
     */
    #[DataProvider('ipvFutureProvider')]
    #[Depends('testOnlyIpvfuture')]
    public function testVersionsAllowedIpvfuture(string $ip, bool $expected): void
    {
        $validator = new Ip([
            'allowipv4'      => false,
            'allowipv6'      => false,
            'allowipvfuture' => true,
            'allowliteral'   => false,
        ]);

        self::assertSame($expected, $validator->isValid($ip));
    }

    public function testNoValidation(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Nothing to validate');

        new Ip([
            'allowipv4'      => false,
            'allowipv6'      => false,
            'allowipvfuture' => false,
            'allowliteral'   => false,
        ]);
    }

    public function testInvalidIpForLaminas4809(): void
    {
        $validator = new Ip();
        self::assertFalse($validator->isValid('1.2.333'));
    }

    public function testInvalidIpForLaminas435(): void
    {
        $validator = new Ip();
        self::assertFalse($validator->isValid('192.168.0.2 adfs'));
    }

    #[Group('Laminas-2694')]
    #[Group('Laminas-8253')]
    public function testIPv6addresses(): void
    {
        $ips = [
            '2001:0db8:0000:0000:0000:0000:1428:57ab'      => true,
            '2001:0DB8:0000:0000:0000:0000:1428:57AB'      => true,
            '[2001:0DB8:0000:0000:0000:0000:1428:57AB]'    => true,
            '2001:00db8:0000:0000:0000:0000:1428:57ab'     => false,
            '2001:0db8:xxxx:0000:0000:0000:1428:57ab'      => false,
            '2001:0DB8:0000:0000:0000:0000:1428:57AB:90'   => false,
            '[2001:0DB8:0000:0000:0000:0000:1428:57AB]:90' => false,
            '2001:db8::1428:57ab'                          => true,
            '2001:db8::1428::57ab'                         => false,
            '2001:dx0::1234'                               => false,
            '2001:db0::12345'                              => false,
            ''                                             => false,
            ':'                                            => false,
            '::'                                           => true,
            ':::'                                          => false,
            '::::'                                         => false,
            '::1'                                          => true,
            ':::1'                                         => false,
            '[::1.2.3.4]'                                  => true,
            '::1.2.3.4'                                    => true,
            '::127.0.0.1'                                  => true,
            '::256.0.0.1'                                  => false,
            '::01.02.03.04'                                => true,
            // according to RFC this can be interpreted as hex notation IPv4
            'a:b:c::1.2.3.4'               => true,
            'a:b:c:d::1.2.3.4'             => true,
            'a:b:c:d:e::1.2.3.4'           => true,
            'a:b:c:d:e:f:1.2.3.4'          => true,
            'a:b:c:d:e:f:1.256.3.4'        => false,
            'a:b:c:d:e:f::1.2.3.4'         => false,
            'a:b:c:d:e:f:0:1:2'            => false,
            'a:b:c:d:e:f:0:1'              => true,
            'a::b:c:d:e:f:0:1'             => false,
            'a::c:d:e:f:0:1'               => true,
            'a::d:e:f:0:1'                 => true,
            'a::e:f:0:1'                   => true,
            'a::f:0:1'                     => true,
            'a::0:1'                       => true,
            'a::1'                         => true,
            'a::'                          => true,
            '::0:1:a:b:c:d:e:f'            => false,
            '::0:a:b:c:d:e:f'              => true,
            '::a:b:c:d:e:f'                => true,
            '::b:c:d:e:f'                  => true,
            '::c:d:e:f'                    => true,
            '::d:e:f'                      => true,
            '::e:f'                        => true,
            '::f'                          => true,
            '0:1:a:b:c:d:e:f::'            => false,
            '0:a:b:c:d:e:f::'              => true,
            'a:b:c:d:e:f::'                => true,
            'b:c:d:e:f::'                  => true,
            'c:d:e:f::'                    => true,
            'd:e:f::'                      => true,
            'e:f::'                        => true,
            'f::'                          => true,
            'a:b:::e:f'                    => false,
            '::a:'                         => false,
            '::a::'                        => false,
            ':a::b'                        => false,
            'a::b:'                        => false,
            '::a:b::c'                     => false,
            'abcde::f'                     => false,
            ':10.0.0.1'                    => false,
            '0:0:0:255.255.255.255'        => false,
            '1fff::a88:85a3::172.31.128.1' => false,
            'a:b:c:d:e:f:0::1'             => false,
            'a:b:c:d:e:f:0::'              => true,
            'a:b:c:d:e:f::0'               => true,
            'total gibberish'              => false,
        ];

        $validator = new Ip();

        foreach ($ips as $ip => $expectedOutcome) {
            if ($expectedOutcome) {
                self::assertTrue($validator->isValid($ip), $ip . ' failed validation (expects true)');
            } else {
                self::assertFalse($validator->isValid($ip), $ip . ' failed validation (expects false)');
            }
        }
    }

    /**
     * @Laminas-4352
     */
    public function testNonStringValidation(): void
    {
        $validator = new Ip();
        self::assertFalse($validator->isValid([1 => 1]));
    }

    /**
     * @Laminas-8640
     */
    public function testNonNewlineValidation(): void
    {
        $validator = new Ip();
        self::assertFalse($validator->isValid("::C0A8:2\n"));
    }

    #[Group('Laminas-10621')]
    public function testIPv4AddressNotations(): void
    {
        $ips = [
            // binary notation
            '00000001.00000010.00000011.00000100'    => true,
            '10000000.02000000.00000000.00000001'    => false,
            '10000000.02000000.00000000.00000001:80' => false,

            // octal notation (always seen as integer!)
            '001.002.003.004'    => true,
            '009.008.007.006'    => true,
            '0a0.100.001.010'    => false,
            '0a0.100.001.010:80' => false,

            // hex notation
            '01.02.03.04'    => true,
            'a0.b0.c0.d0'    => true,
            'g0.00.00.00'    => false,
            'g0.00.00.00:80' => false,

            // new lines should not accept
            "00000001.00000010.00000011.00000100\n" => false,
            "001.002.003.004\n"                     => false,
            "a0.b0.c0.d0\n"                         => false,
        ];

        $validator = new Ip([
            'allowipv4'      => true,
            'allowipv6'      => false,
            'allowipvfuture' => false,
            'allowliteral'   => false,
        ]);

        foreach ($ips as $ip => $expectedOutcome) {
            if ($expectedOutcome) {
                self::assertTrue($validator->isValid($ip), $ip . ' failed validation (expects true)');
            } else {
                self::assertFalse($validator->isValid($ip), $ip . ' failed validation (expects false)');
            }
        }
    }

    #[DataProvider('iPvFutureAddressesProvider')]
    public function testIPvFutureAddresses(string $ip, bool $expected): void
    {
        $validator = new Ip([
            'allowipv4'      => false,
            'allowipv6'      => false,
            'allowipvfuture' => true,
            'allowliteral'   => true,
        ]);

        self::assertSame($expected, $validator->isValid($ip));
    }

    /**
     * @psalm-return array<array-key, array{0: string, 1: bool}>
     */
    public static function iPvFutureAddressesProvider(): array
    {
        return [
            ["[v1.09azAZ-._~!$&'()*+,;=:]:80", false],
            ["[v1.09azAZ-._~!$&'()*+,;=:]", true],
            ["[v1.09azAZ-._~!$&'()*+,;=:", false],
            ["v1.09azAZ-._~!$&'()*+,;=:]", false],
            ["v1.09azAZ-._~!$&'()*+,;=:", true],
            ["v1.09azAZ-._~!$&'()*+,;=", true],
            ["v1.09azAZ-._~!$&'()*+,;", true],
            ["v1.09azAZ-._~!$&'()*+,", true],
            ["v1.09azAZ-._~!$&'()*+", true],
            ["v1.09azAZ-._~!$&'()*", true],
            ["v1.09azAZ-._~!$&'()", true],
            ["v1.09azAZ-._~!$&'(", true],
            ["v1.09azAZ-._~!$&'", true],
            ['v1.09azAZ-._~!$&', true],
            ['v1.09azAZ-._~!$', true],
            ['v1.09azAZ-._~!', true],
            ['v1.09azAZ-._~', true],
            ['v1.09azAZ-._', true],
            ['v1.09azAZ-.', true],
            ['v1.09azAZ-', true],
            ['v1.09azAZ', true],
            ['v1.09azA', true],
            ['v1.09az', true],
            ['v1.09a', true],
            ['v1.09', true],
            ['v1.0', true],
            ['v1.', false],
            ['v1', false],
            ['v', false],
            ['', false],
            ['vFF.Z', true],
            ['vFG./', false],
            ['v1./', false],
            ['v1.?', false],
            ['v1.#', false],
            ['v1.[', false],
            ['v1.]', false],
            ['v1.@', false],
        ];
    }

    /**
     * Ensures that getMessages() returns expected default value
     */
    public function testGetMessages(): void
    {
        $validator = new Ip();
        self::assertSame([], $validator->getMessages());
    }

    /**
     * @psalm-return array<string, array{0: string}>
     */
    public static function invalidIpV4Addresses(): array
    {
        return [
            'all-numeric'          => ['111111111111'],
            'first-quartet'        => ['111.111111111'],
            'first-octet'          => ['111111.111111'],
            'last-quartet'         => ['111111111.111'],
            'first-second-quartet' => ['111.111.111111'],
            'first-fourth-quartet' => ['111.111111.111'],
            'third-fourth-quartet' => ['111111.111.111'],
        ];
    }

    #[DataProvider('invalidIpV4Addresses')]
    public function testIpV4ValidationShouldFailForIpV4AddressesMissingQuartets(string $address): void
    {
        $validator = new Ip([
            'allowipv4'      => true,
            'allowipv6'      => false,
            'allowipvfuture' => false,
            'allowliteral'   => false,
        ]);
        self::assertFalse($validator->isValid($address));
    }
}
