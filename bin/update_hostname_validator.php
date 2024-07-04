<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

const IANA_URL                        = 'https://data.iana.org/TLD/tlds-alpha-by-domain.txt';
const LAMINAS_HOSTNAME_VALIDATOR_FILE = __DIR__ . '/../src/Hostname.php';

if (! file_exists(LAMINAS_HOSTNAME_VALIDATOR_FILE) || ! is_readable(LAMINAS_HOSTNAME_VALIDATOR_FILE)) {
    printf("Error: cannot read file '%s'%s", LAMINAS_HOSTNAME_VALIDATOR_FILE, PHP_EOL);
    exit(1);
}

if (! is_writable(LAMINAS_HOSTNAME_VALIDATOR_FILE)) {
    printf("Error: cannot update file '%s'%s", LAMINAS_HOSTNAME_VALIDATOR_FILE, PHP_EOL);
    exit(1);
}

if (! extension_loaded('intl')) {
    printf("Error: ext-intl is required by this script%s", PHP_EOL);
    exit(1);
}

/** @psalm-var list<string> $newFileContent */
$newFileContent = [];    // new file content
$insertDone     = false; // becomes 'true' when we find start of $validTlds declaration
$insertFinish   = false; // becomes 'true' when we find end of $validTlds declaration
$checkOnly      = isset($argv[1]) ? $argv[1] === '--check-only' : false;
$response       = getOfficialTLDs();

$currentFileContent = file(LAMINAS_HOSTNAME_VALIDATOR_FILE);

foreach ($currentFileContent as $line) {
    if ($insertDone === $insertFinish) {
        // Outside of $validTlds definition; keep line as-is
        $newFileContent[] = $line;
    }

    if ($insertFinish) {
        continue;
    }

    if ($insertDone) {
        // Detect where the $validTlds declaration ends
        if (preg_match('/^\s+\];\s*$/', $line)) {
            $newFileContent[] = $line;
            $insertFinish     = true;
        }

        continue;
    }

    // Detect where the $validTlds declaration begins
    if (preg_match('/^\s+private\s+array\s+\$validTlds\s+=\s+\[\s*$/', $line)) {
        $newFileContent = array_merge($newFileContent, getNewValidTlds($response));
        $insertDone     = true;
    }
}

if (! $insertDone) {
    printf('Error: cannot find line with "private array $validTlds"%s', PHP_EOL);
    exit(1);
}

if (! $insertFinish) {
    printf('Error: cannot find end of $validTlds declaration%s', PHP_EOL);
    exit(1);
}

if ($currentFileContent === $newFileContent) {
    printf('Nothing to do. Validator has no TLD changes.%s', PHP_EOL);
    exit(0);
}

if ($checkOnly) {
    printf(
        'TLDs must be updated, please run `php bin/update_hostname_validator.php` and push your changes%s',
        PHP_EOL
    );
    exit(1);
}

if (false === @file_put_contents(LAMINAS_HOSTNAME_VALIDATOR_FILE, $newFileContent)) {
    printf('Error: cannot write info file "%s"%s', LAMINAS_HOSTNAME_VALIDATOR_FILE, PHP_EOL);
    exit(1);
}

printf('Validator TLD file updated.%s', PHP_EOL);
exit(0);

/**
 * Get Official TLDs
 */
function getOfficialTLDs(): string
{
    try {
        return file_get_contents(IANA_URL);
    } catch (Throwable $e) {
        printf(
            'Downloading the IANA TLD list failed: %s',
            $e->getMessage(),
        );

        exit(1);
    }
}

/**
 * Extract new Valid TLDs from a string containing one per line.
 *
 * @return list<string>
 */
function getNewValidTlds(string $string): array
{
    // Get new TLDs from the list previously fetched
    $newValidTlds = [];
    foreach (preg_grep('/^[^#]/', preg_split("#\r?\n#", $string)) as $line) {
        $newValidTlds [] = sprintf(
            "%s'%s',\n",
            str_repeat(' ', 8),
            idn_to_utf8(strtolower($line), 0, INTL_IDNA_VARIANT_UTS46),
        );
    }

    return $newValidTlds;
}
