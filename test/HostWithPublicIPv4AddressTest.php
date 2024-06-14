<?php

declare(strict_types=1);

namespace LaminasTest\Validator;

use Laminas\Validator\HostWithPublicIPv4Address;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class HostWithPublicIPv4AddressTest extends TestCase
{
    public function testNonStringInput(): void
    {
        $validator = new HostWithPublicIPv4Address();
        self::assertFalse($validator->isValid(123));
        $messages = $validator->getMessages();
        self::assertArrayHasKey(HostWithPublicIPv4Address::ERROR_NOT_STRING, $messages);
        self::assertSame(
            'Expected a string hostname but received int',
            $messages[HostWithPublicIPv4Address::ERROR_NOT_STRING],
        );
    }

    public function testUnresolvableHostname(): void
    {
        $validator = new HostWithPublicIPv4Address();
        self::assertFalse($validator->isValid('foo'));
        $messages = $validator->getMessages();
        self::assertArrayHasKey(HostWithPublicIPv4Address::ERROR_HOSTNAME_NOT_RESOLVED, $messages);
        self::assertSame(
            'The hostname "foo" cannot be resolved',
            $messages[HostWithPublicIPv4Address::ERROR_HOSTNAME_NOT_RESOLVED],
        );
    }

    public function testHostnameThatResolvesToAPrivateIp(): void
    {
        $validator = new HostWithPublicIPv4Address();
        self::assertFalse($validator->isValid('localhost'));
        $messages = $validator->getMessages();
        self::assertArrayHasKey(HostWithPublicIPv4Address::ERROR_PRIVATE_IP_FOUND, $messages);
        self::assertSame(
            'The hostname "localhost" resolves to at least one reserved IPv4 address',
            $messages[HostWithPublicIPv4Address::ERROR_PRIVATE_IP_FOUND],
        );
    }

    /** @return list<array{0: string}> */
    public static function reservedIpProvider(): array
    {
        return [
            // 0.0.0.0/8
            ['0.0.0.0'],
            ['0.255.255.255'],

            // 10.0.0.0/8
            ['10.0.0.0'],
            ['10.255.255.255'],

            // 127.0.0.0/8
            ['127.0.0.0'],
            ['127.255.255.255'],

            // 100.64.0.0/10
            ['100.64.0.0'],
            ['100.127.255.255'],

            // 172.16.0.0/12
            ['172.16.0.0'],
            ['172.31.255.255'],

            // 198.18.0.0./15
            ['198.18.0.0'],
            ['198.19.255.255'],

            // 169.254.0.0/16
            ['169.254.0.0'],
            ['169.254.255.255'],

            // 192.168.0.0/16
            ['192.168.0.0'],
            ['192.168.255.25'],

            // 192.0.2.0/24
            ['192.0.2.0'],
            ['192.0.2.255'],

            // 192.88.99.0/24
            ['192.88.99.0'],
            ['192.88.99.255'],

            // 198.51.100.0/24
            ['198.51.100.0'],
            ['198.51.100.255'],

            // 203.0.113.0/24
            ['203.0.113.0'],
            ['203.0.113.255'],

            // 224.0.0.0/4
            ['224.0.0.0'],
            ['239.255.255.255'],

            // 240.0.0.0/4
            ['240.0.0.0'],
            ['255.255.255.254'],

            // 255.255.255.255/32
            ['255.255.55.255'],
        ];
    }

    #[DataProvider('reservedIpProvider')]
    public function testAReservedIpIsInvalid(string $reservedIp): void
    {
        $validator = new HostWithPublicIPv4Address();
        self::assertFalse($validator->isValid($reservedIp));
        $messages = $validator->getMessages();
        self::assertArrayHasKey(HostWithPublicIPv4Address::ERROR_PRIVATE_IP_FOUND, $messages);
        self::assertSame(
            'The hostname "' . $reservedIp . '" resolves to at least one reserved IPv4 address',
            $messages[HostWithPublicIPv4Address::ERROR_PRIVATE_IP_FOUND],
        );
    }

    /** @return list<array{0: string}> */
    public static function releasedReservedIpProvider(): array
    {
        return [
            // 128.0.0.0/16
            ['128.0.0.0'],
            ['128.0.255.255'],

            // 191.255.0.0/16
            ['191.255.0.0'],
            ['191.255.255.255'],

            // 223.255.255.0/24
            ['223.255.255.0'],
            ['223.255.255.255'],
        ];
    }

    #[DataProvider('releasedReservedIpProvider')]
    public function testPreviouslyReservedIpIsValid(string $ip): void
    {
        $validator = new HostWithPublicIPv4Address();
        self::assertTrue($validator->isValid($ip));
    }

    public function testAHostnameThatResolvesToPublicIPv4AddressIsValid(): void
    {
        $validator = new HostWithPublicIPv4Address();
        self::assertTrue($validator->isValid('example.com'));
    }
}
