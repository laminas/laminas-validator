<?php

declare(strict_types=1);

namespace Laminas\Validator;

use function get_debug_type;
use function gethostbynamel;
use function is_array;
use function is_string;
use function preg_match;

final class HostWithPublicIPv4Address extends AbstractValidator
{
    public const ERROR_NOT_STRING            = 'hostnameNotString';
    public const ERROR_HOSTNAME_NOT_RESOLVED = 'hostnameNotResolved';
    public const ERROR_PRIVATE_IP_FOUND      = 'privateIpAddressFound';

    /** @var array<non-empty-string, non-empty-string> */
    protected array $messageTemplates = [
        self::ERROR_NOT_STRING            => 'Expected a string hostname but received %type%',
        self::ERROR_HOSTNAME_NOT_RESOLVED => 'The hostname "%value%" cannot be resolved',
        self::ERROR_PRIVATE_IP_FOUND      => 'The hostname "%value%" resolves to at least one reserved IPv4 address',
    ];

    protected string $type = 'null';

    /** @var array<non-empty-string, non-empty-string> */
    protected array $messageVariables = [
        'type'  => 'type',
        'value' => 'value',
    ];

    public function isValid(mixed $value): bool
    {
        $this->type = get_debug_type($value);

        if (! is_string($value)) {
            $this->error(self::ERROR_NOT_STRING);

            return false;
        }

        $this->value = $value;

        if (! preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}$/', $value)) {
            $addressList = gethostbynamel($value);
        } else {
            $addressList = [$value];
        }

        if (! is_array($addressList)) {
            $this->error(self::ERROR_HOSTNAME_NOT_RESOLVED);

            return false;
        }

        $privateAddressWasFound = false;

        // phpcs:disable Generic.Files.LineLength
        foreach ($addressList as $server) {
            // Search for 0.0.0.0/8, 10.0.0.0/8, 127.0.0.0/8
            if (
                preg_match('/^(0|10|127)(\.([0-9]|[1-9][0-9]|1([0-9][0-9])|2([0-4][0-9]|5[0-5]))){3}$/', $server)
                ||
                // Search for 100.64.0.0/10
                preg_match('/^100\.(6[0-4]|[7-9][0-9]|1[0-1][0-9]|12[0-7])(\.([0-9]|[1-9][0-9]|1([0-9][0-9])|2([0-4][0-9]|5[0-5]))){2}$/', $server)
                ||
                // Search for 172.16.0.0/12
                preg_match('/^172\.(1[6-9]|2[0-9]|3[0-1])(\.([0-9]|[1-9][0-9]|1([0-9][0-9])|2([0-4][0-9]|5[0-5]))){2}$/', $server)
                ||
                // Search for 198.18.0.0/15
                preg_match('/^198\.(1[8-9])(\.([0-9]|[1-9][0-9]|1([0-9][0-9])|2([0-4][0-9]|5[0-5]))){2}$/', $server)
                ||
                // Search for 169.254.0.0/16, 192.168.0.0/16
                preg_match('/^(169\.254|192\.168)(\.([0-9]|[1-9][0-9]|1([0-9][0-9])|2([0-4][0-9]|5[0-5]))){2}$/', $server)
                ||
                // Search for 192.0.2.0/24, 192.88.99.0/24, 198.51.100.0/24, 203.0.113.0/24
                preg_match('/^(192\.0\.2|192\.88\.99|198\.51\.100|203\.0\.113)\.([0-9]|[1-9][0-9]|1([0-9][0-9])|2([0-4][0-9]|5[0-5]))$/', $server)
                ||
                // Search for 224.0.0.0/4, 240.0.0.0/4
                preg_match('/^(2(2[4-9]|[3-4][0-9]|5[0-5]))(\.([0-9]|[1-9][0-9]|1([0-9][0-9])|2([0-4][0-9]|5[0-5]))){3}$/', $server)
            ) {
                $privateAddressWasFound = true;

                break;
            }
        }

        if ($privateAddressWasFound) {
            $this->error(self::ERROR_PRIVATE_IP_FOUND);

            return false;
        }

        return true;
    }
}
