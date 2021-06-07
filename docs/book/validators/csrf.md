# CSRF Validator

`Laminas\Validator\Csrf` provides the ability to both generate and validate CSRF tokens.
This allows you to validate if a form submission originated from the same site, by confirming the value of the CSRF field in the submitted form is the same as the one contained in the original form.

[Cross-Site Request Forgery (CSRF)](https://en.wikipedia.org/wiki/Cross-site_request_forgery) is a security vector in which an unauthorized request is accepted by a server on behalf of another user; it is essentially an exploit of the trust a site places on a user's browser.

The typical mitigation is to create a one-time token that is transmitted as part of the original form, and which must then be transmitted back by the client.
This token expires after first submission or after a short amount of time, preventing replays or further submissions.
If the token provided does not match what was originally sent, an error should be returned.

## Installation Requirements

The CSRF validator depends on [laminas-math](https://docs.laminas.dev/laminas-math/) to generate the hash and on [laminas-session](https://docs.laminas.dev/laminas-session/) to persist the generated token between requests.
Consequently, both components must be installed before the validator can be used.
To install both components, run the following command.

```bash
composer require laminas/laminas-math laminas/laminas-session
```

## Supported Options

The following options are supported for `Laminas\Validator\Csrf`.

| Option | Description | Optional/Mandatory |
|-|-|-|
| `name` | The name of the CSRF element | Optional |
| `salt` | The salt for the CSRF token | Optional |
| `session` | The name of the session element containing the CSRF element | **Mandatory** |
| `timeout` | The [TTL](https://en.wikipedia.org/wiki/Time_to_live) for the CSRF token | Optional |

## Basic Usage

Here is a basic example.

```php
// Initialise a new session container
// or use the existing one in your application
$session = new Laminas\Session\Container();

// Create the validator
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
