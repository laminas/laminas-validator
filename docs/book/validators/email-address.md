# EmailAddress Validator

`Laminas\Validator\EmailAddress` allows you to validate an email address. The
validator first splits the email address on `local-part @ hostname` and attempts
to match these against known specifications for email addresses and hostnames.

## Basic usage

A basic example of usage is below:

```php
$validator = new Laminas\Validator\EmailAddress();

if ($validator->isValid($email)) {
    // email appears to be valid
} else {
    // email is invalid; print the reasons
    foreach ($validator->getMessages() as $message) {
        echo "$message\n";
    }
}
```

This will match the email address `$email` and on failure populate
`getMessages()` with useful error messages.

## Supported Options

`Laminas\Validator\EmailAddress` supports several options which can either be set
at instantiation, by giving an array with the related options, or afterwards, by
using `setOptions()`. The following options are supported:

- `allow`: Defines which type of domain names are accepted. This option is used
  in conjunction with the hostnameValidator option to set the hostname validator.
  Possible values of this option defined in [Hostname](hostname.md) validator's
  `ALLOW_*` constants:
    - `ALLOW_DNS` (default) - Allows Internet domain names _(e.g. example.com)_
    - `ALLOW_IP` - Allows IP addresses _(e.g. 192.168.0.1)_
    - `ALLOW_LOCAL` - Allows local network such as _localhost_ or _www.localdomain_
    - `ALLOW_URI`  - Allows hostnames in URI generic syntax. See [RFC 3986](https://www.ietf.org/rfc/rfc3986.txt)
    - `ALLOW_ALL` - Allows all types of hostnames

- `useDeepMxCheck`: Defines if the servers MX records should be verified by a deep check.
  When this option is set to `true` then additionally to MX records also the `A`,
  `A6` and `AAAA` records are used to verify if the server accepts emails. This
  option defaults to `false`.
- `useDomainCheck`: Defines if the domain part should be checked. When this option is
  set to `false`, then only the local part of the email address will be checked.
  In this case the hostname validator will not be called. This option defaults
  to `true`.
- `hostnameValidator`: Sets the hostname validator object instance with which the
  domain part of the email address will be validated.
- `useMxCheck`: Defines if the MX records from the server should be detected. If this
  option is defined to `true` then the MX records are used to verify if the
  server accepts emails. This option defaults to `false`.


## Complex local parts

`Laminas\Validator\EmailAddress` will match any valid email address according to
RFC2822. For example, valid emails include `bob@domain.com`,
`bob+jones@domain.us`, `"bob@jones"@domain.com*` and `"bob jones"@domain.com`

Some obsolete email formats will not currently validate (e.g. carriage returns
or a `\\` character in an email address).

## Validating only the local part

If you need `Laminas\Validator\EmailAddress` to check only the local part of an
email address, and want to disable validation of the hostname, you can set the
`domain` option to `false`. This forces `Laminas\Validator\EmailAddress` not to
validate the hostname part of the email address.

```php
$validator = new Laminas\Validator\EmailAddress();
$validator->setOptions(['domain' => FALSE]);
```

## Validating different types of hostnames

The hostname part of an email address is validated against the [Hostname validator](hostname.md).
By default only DNS hostnames of the form `domain.com` are accepted, though if
you wish you can accept IP addresses and Local hostnames too.

To do this you need to instantiate `Laminas\Validator\EmailAddress` passing a
parameter to indicate the type of hostnames you want to accept. More details are
included in `Laminas\Validator\Hostname`, though an example of how to accept both
DNS and Local hostnames appears below:

```php
use Laminas\Validator\EmailAddress;
use Laminas\Validator\Hostname;

$validator = new EmailAddress( Hostname::ALLOW_DNS | Hostname::ALLOW_LOCAL);

if ($validator->isValid($email)) {
    // email appears to be valid
} else {
    // email is invalid; print the reasons
    foreach ($validator->getMessages() as $message) {
        echo "$message\n";
    }
}
```

## Checking if the hostname actually accepts email

Just because an email address is in the correct format, it doesn't necessarily
mean that email address actually exists. To help solve this problem, you can use
MX validation to check whether an MX (email) entry exists in the DNS record for
the email's hostname. This tells you that the hostname accepts email, but
doesn't tell you the exact email address itself is valid.

MX checking is not enabled by default. To enable MX checking you can pass a
second parameter to the `Laminas\Validator\EmailAddress` constructor.

```php
$validator = new Laminas\Validator\EmailAddress([
    'allow' => Laminas\Validator\Hostname::ALLOW_DNS,
    'useMxCheck'    => true,
]);
```

Alternatively you can either pass `true` or `false` to `setValidateMx()` to
enable or disable MX validation.

By enabling this setting, network functions will be used to check for the
presence of an MX record on the hostname of the email address you wish to
validate. Please be aware this will likely slow your script down.

Sometimes validation for MX records returns `false`, even if emails are
accepted. The reason behind this behaviour is, that servers can accept emails
even if they do not provide a MX record. In this case they can provide `A`,
`A6`, or `AAAA` records. To allow `Laminas\Validator\EmailAddress` to check also
for these other records, you need to set deep MX validation. This can be done at
initiation by setting the `deep` option or by using `setOptions()`.

```php
$validator = new Laminas\Validator\EmailAddress([
    'allow' => Laminas\Validator\Hostname::ALLOW_DNS,
    'useMxCheck'    => true,
    'useDeepMxCheck'  => true,
]);
```

Sometimes it can be useful to get the server's MX information which have been
used to do further processing. Simply use `getMXRecord()` after validation. This
method returns the received MX record including weight and sorted by it.

> ### Performance warning**
>
> You should be aware that enabling MX check will slow down you script because
> of the used network functions. Enabling deep check will slow down your script
> even more as it searches the given server for 3 additional types.

> ### Disallowed IP addresses
>
> You should note that MX validation is only accepted for external servers. When
> deep MX validation is enabled, then local IP addresses like `192.168.*` or
> `169.254.*` are not accepted.

## Validating International Domains Names

`Laminas\Validator\EmailAddress` will also match international characters that
exist in some domains. This is known as International Domain Name (IDN) support.
This is enabled by default, though you can disable this by changing the setting
via the internal `Laminas\Validator\Hostname` object that exists within
`Laminas\Validator\EmailAddress`.

```php
$validator->getHostnameValidator()->setValidateIdn(false);
```

More information on the usage of `setValidateIdn()` appears in the
[Hostname documentation](hostname.md).

Please note IDNs are only validated if you allow DNS hostnames to be validated.

## Validating Top Level Domains

By default a hostname will be checked against a list of known TLDs. This is
enabled by default, though you can disable this by changing the setting via the
internal `Laminas\Validator\Hostname` object that exists within
`Laminas\Validator\EmailAddress`.

```php
$validator->getHostnameValidator()->setValidateTld(false);
```

More information on the usage of `setValidateTld()` appears in the
[Hostname documentation](hostname.md).

Please note TLDs are only validated if you allow DNS hostnames to be validated.

## Setting messages

`Laminas\Validator\EmailAddress` makes also use of `Laminas\Validator\Hostname` to
check the hostname part of a given email address. You can specify messages for
`Laminas\Validator\Hostname` from within `Laminas\Validator\EmailAddress`.

```php
$validator = new Laminas\Validator\EmailAddress();
$validator->setMessages([
    Laminas\Validator\Hostname::UNKNOWN_TLD => 'I don\'t know the TLD you gave'
]);
```
