<?php

/**
 * @see       https://github.com/laminas/laminas-validator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-validator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-validator/blob/master/LICENSE.md New BSD License
 */

use Laminas\Http\Client;
use Laminas\Validator\Hostname;

require __DIR__ . '/../vendor/autoload.php';

define('IANA_URL', 'https://data.iana.org/TLD/tlds-alpha-by-domain.txt');
define('Laminas_HOSTNAME_VALIDATOR_FILE', __DIR__.'/../src/Hostname.php');

if (! file_exists(Laminas_HOSTNAME_VALIDATOR_FILE) || ! is_readable(Laminas_HOSTNAME_VALIDATOR_FILE)) {
    printf("Error: cannont read file '%s'%s", Laminas_HOSTNAME_VALIDATOR_FILE, PHP_EOL);
    exit(1);
}

if (! is_writable(Laminas_HOSTNAME_VALIDATOR_FILE)) {
    printf("Error: Cannot update file '%s'%s", Laminas_HOSTNAME_VALIDATOR_FILE, PHP_EOL);
    exit(1);
}

// get current list of official TLDs
$client = new Client();
$client->setOptions([
    'adapter' => 'Laminas\Http\Client\Adapter\Curl',
]);
$client->setUri(IANA_URL);
$client->setMethod('GET');
$response = $client->send();
if (! $response->isSuccess()) {
    printf("Error: cannot get '%s'%s", IANA_URL, PHP_EOL);
    exit(1);
}

$decodePunycode = getPunycodeDecoder();

// Get new TLDs from the list previously fetched
$newValidTlds = [];
foreach (preg_grep('/^[^#]/', preg_split("#\r?\n#", $response->getBody())) as $line) {
    $newValidTlds []= sprintf(
        "%s'%s',\n",
        str_repeat(' ', 8),
        $decodePunycode(strtolower($line))
    );
}

$newFileContent = [];  // new file content
$insertDone     = false;    // becomes 'true' when we find start of $validTlds declaration
$insertFinish   = false;    // becomes 'true' when we find end of $validTlds declaration
foreach (file(Laminas_HOSTNAME_VALIDATOR_FILE) as $line) {
    if ($insertDone === $insertFinish) {
        // Outside of $validTlds definition; keep line as-is
        $newFileContent []= $line;
    }

    if ($insertFinish) {
        continue;
    }

    if ($insertDone) {
        // Detect where the $validTlds declaration ends
        if (preg_match('/^\s+\);\s*$/', $line)) {
            $newFileContent []= $line;
            $insertFinish = true;
        }

        continue;
    }

    // Detect where the $validTlds declaration begins
    if (preg_match('/^\s+protected\s+\$validTlds\s+=\s+array\(\s*$/', $line)) {
        $newFileContent = array_merge($newFileContent, $newValidTlds);
        $insertDone = true;
    }
}

if (! $insertDone) {
    printf("Error: cannot find line with 'protected \$validTlds'%s", PHP_EOL);
    exit(1);
}

if (!$insertFinish) {
    printf("Error: cannot find end of \$validTlds declaration%s", PHP_EOL);
    exit(1);
}

if (false === @file_put_contents(Laminas_HOSTNAME_VALIDATOR_FILE, $newFileContent)) {
    printf("Error: cannot write info file '%s'%s", Laminas_HOSTNAME_VALIDATOR_FILE, PHP_EOL);
    exit(1);
}

printf("Validator TLD file updated.%s", PHP_EOL);
exit(0);

/**
 * Retrieve and return a punycode decoder.
 *
 * TLDs are puny encoded.
 *
 * We need a decodePunycode function to translate TLDs to UTF-8:
 *
 * - use idn_to_utf8 if available
 * - otherwise, use Hostname::decodePunycode()
 *
 * @return callable
 */
function getPunycodeDecoder()
{
    if (function_exists('idn_to_utf8')) {
        return 'idn_to_utf8';
    }

    $hostnameValidator = new Hostname();
    $reflection = new ReflectionClass(get_class($hostnameValidator));
    $method = $reflection->getMethod('decodePunycode');
    $method->setAccessible(true);

    return function ($encode) use ($hostnameValidator, $method) {
        if (strpos($encode, 'xn--') === 0) {
            return $method->invokeArgs($hostnameValidator, [substr($encode, 4)]);
        }
        return $encode;
    };
}
