# CSRF Validator

`Laminas\Validator\Csrf` provides the ability to both generate and validate CSRF tokens.
This allows you to validate if a form submission originated from the same site, by confirming the value of the CSRF field in the submitted form is the same as the one contained in the original form.

[Cross-Site Request Forgery (CSRF)](https://en.wikipedia.org/wiki/Cross-site_request_forgery) is a security vector in which an unauthorized request is accepted by a server on behalf of another user; it is essentially an exploit of the trust a site places on a user's browser.

The typical mitigation is to create a one-time token that is transmitted as part of the original form, and which must then be transmitted back by the client.
This token expires after first submission or after a short amount of time, preventing replays or further submissions.
If the token provided does not match what was originally sent, an error should be returned.

## Supported options

The following options are supported for `Laminas\Validator\Csrf`.

| Option | Description | Optional/Mandatory |
|-|-|-|
| `name` | The name of the CSRF element | Optional |
| `salt` | The salt for the CSRF token | Optional |
| `session` | The name of the session element containing the CSRF element | **Mandatory** |
| `timeout` | The [TTL](https://en.wikipedia.org/wiki/Time_to_live) for the CSRF token | Optional |

## Library requirements

Before you can use this validator, you have to install the following, additional, packages:

- [laminas-math](https://docs.laminas.dev/laminas-math/)
- [laminas-session](https://docs.laminas.dev/laminas-session/)

## Basic usages

Here is a basic, working, example.

```php
<?php

use Laminas\Session\Container;

require_once('vendor/autoload.php');

// Initialise a new session container
// or use the existing one in your application
$session = new Container();
$validator = new Laminas\Validator\Csrf([
    'session' => $session,
]);
$hash = $validator->getHash();

// ...Render the hash in the form.

// Validate the hash after form submission.
echo ($validator->isValid($hash))
    ? "Token is valid"
    : "Token is NOT valid";
```

