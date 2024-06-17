# Host with Public IPv4 Address Validator

`Laminas\Validator\HostWithPublicIPv4Address` allows you to validate that an IP address is not a reserved address such as 127.0.0.1, or that a hostname does not point to a known, reserved address.

## Supported options

This validator has no options

## Basic usage

```php
$validator = new Laminas\Validator\HostWithPublicIPv4Address();

if ($validator->isValid('example.com')) {
    // hostname appears to be valid
} else {
    // hostname is invalid; print the reasons
    foreach ($validator->getMessages() as $message) {
        echo "$message\n";
    }
}
```

```php
$validator = new Laminas\Validator\HostWithPublicIPv4Address();

if ($validator->isValid('192.168.0.1')) {
    // hostname appears to be valid
} else {
    // hostname is invalid; print the reasons
    foreach ($validator->getMessages() as $message) {
        echo "$message\n";
    }
}
```

## Hostnames with multiple records

When validating a hostname as opposed to an IP address, if that hostname resolves to multiple IPv4 addresses and _any_ of those addresses are private or reserved, then the validator will deem the hostname invalid.
