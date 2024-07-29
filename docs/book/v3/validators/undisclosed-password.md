# Undisclosed Password Validator

`Laminas\Validator\UndisclosedPassword` allows you to validate if a given password was found in data breaches using the service [Have I Been Pwned?](https://www.haveibeenpwned.com), in a secure, anonymous way using [K-Anonymity](https://www.troyhunt.com/ive-just-launched-pwned-passwords-version-2) to ensure passwords are not send in full over the wire.

<!-- markdownlint-disable-next-line MD001 -->
> ### Installation requirements
>
> This validator needs to make a request over HTTP; therefore it requires an HTTP client. The validator provides support only for HTTP clients implementing [PSR-18](https://www.php-fig.org/psr/psr-18/) and [PSR-17](https://www.php-fig.org/psr/psr-17/) request and response factories.
>
> To ensure you have these installed before using this validator, run the following:
>
> ```bash
> $ composer require psr/http-client
> $ composer require psr/http-factory
> ```

## Basic usage

The validator has two required constructor arguments:

- an HTTP Client that implements `Psr\Http\Client\ClientInterface`
- a `Psr\Http\Message\RequestFactoryInterface` instance

Once you have an instance, you can then pass a password to its `isValid()` method to determine if it has been disclosed in a known data breach.

If the password was found via the service, `isValid()` will return `false`. If the password was not found, `isValid()` will return `true`.

```php
$validator = new Laminas\Validator\UndisclosedPassword(
    $httpClient, // a PSR-18 HttpClientInterface
    $requestFactory // a PSR-17 RequestFactoryInterface
);

$result = $validator->isValid('password');
// $result is FALSE because "password" was found in a data breach

$result = $validator->isValid('8aDk=XiW2E.77tLfuAcB');
// $result is TRUE because "8aDk=XiW2E.77tLfuAcB" was not found in a data breach
```

## A simple command line example

In this example, I'm using `laminas/laminas-diactoros` to provide HTTP messages, and `php-http/curl-client` as the HTTP client. Let's begin with installation of all required packages:

```bash
$ composer require \
    php-http/message \
    php-http/message-factory \
    php-http/discovery \
    php-http/curl-client \
    laminas/laminas-diactoros \
    laminas/laminas-validator
```

Next, I create a file, `undisclosed.php`, where I put my code:

```php
require_once __DIR__ . '/vendor/autoload.php';

use Http\Client\Curl\Client;
use Laminas\Diactoros\RequestFactory;
use Laminas\Diactoros\ResponseFactory;
use Laminas\Validator\UndisclosedPassword;

$requestFactory = new RequestFactory();
$responseFactory = new ResponseFactory();
$client = new Client($responseFactory, null);

$undisclosedPassword = new UndisclosedPassword($client, $requestFactory);
echo 'Password "password" is ' . ($undisclosedPassword->isValid('password') ? 'not disclosed' : 'disclosed') . PHP_EOL;
echo 'Password "NVt3MpvQ" is ' . ($undisclosedPassword->isValid('NVt3MpvQ') ? 'not disclosed' : 'disclosed') . PHP_EOL;
```

To run it, I use the PHP command line interpreter:

```bash
$ php undisclosed.php
```

And it gives me the following output:

```bash
Password "password" is disclosed
Password "NVt3MpvQ" is not disclosed
```
