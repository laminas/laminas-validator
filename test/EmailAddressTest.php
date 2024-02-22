<?php

declare(strict_types=1);

namespace LaminasTest\Validator;

use Laminas\Validator\EmailAddress;
use Laminas\Validator\Exception\InvalidArgumentException;
use Laminas\Validator\Hostname;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

use function array_key_exists;
use function array_keys;
use function checkdnsrr;
use function count;
use function current;
use function extension_loaded;
use function getenv;
use function implode;
use function next;
use function preg_replace;
use function set_error_handler;
use function sprintf;
use function str_repeat;

use const E_USER_NOTICE;

final class EmailAddressTest extends TestCase
{
    private EmailAddress $validator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = new EmailAddress();
    }

    /**
     * Ensures that a basic valid e-mail address passes validation
     */
    public function testBasic(): void
    {
        self::assertTrue($this->validator->isValid('username@example.com'));
    }

    /**
     * Ensures that localhost address is valid
     */
    public function testLocalhostAllowed(): void
    {
        $validator = new EmailAddress(Hostname::ALLOW_ALL);

        self::assertTrue($validator->isValid('username@localhost'));
    }

    /**
     * Ensures that local domain names are valid
     */
    public function testLocalDomainAllowed(): void
    {
        $validator = new EmailAddress(Hostname::ALLOW_ALL);

        self::assertTrue($validator->isValid('username@localhost.localdomain'));
    }

    /**
     * Ensures that IP hostnames are valid
     */
    public function testIPAllowed(): void
    {
        $validator      = new EmailAddress(Hostname::ALLOW_DNS | Hostname::ALLOW_IP);
        $valuesExpected = [
            [Hostname::ALLOW_DNS, true, ['bob@212.212.20.4']],
            [Hostname::ALLOW_DNS, false, ['bob@localhost']],
        ];
        foreach ($valuesExpected as $element) {
            foreach ($element[2] as $input) {
                self::assertSame($element[1], $validator->isValid($input), implode("\n", $validator->getMessages()));
            }
        }
    }

    /**
     * Ensures that validation fails when the local part is missing
     */
    public function testLocalPartMissing(): void
    {
        self::assertFalse($this->validator->isValid('@example.com'));

        $messages = $this->validator->getMessages();

        self::assertCount(1, $messages);
        self::assertStringContainsString('local-part@hostname', current($messages));
    }

    /**
     * Ensures that validation fails and produces the expected messages when the local part is invalid
     */
    public function testLocalPartInvalid(): void
    {
        self::assertFalse($this->validator->isValid('Some User@example.com'));

        $messages = $this->validator->getMessages();

        self::assertCount(3, $messages);

        self::assertStringContainsString('Some User', current($messages));
        self::assertStringContainsString('dot-atom', current($messages));

        self::assertStringContainsString('Some User', next($messages));
        self::assertStringContainsString('quoted-string', current($messages));

        self::assertStringContainsString('Some User', next($messages));
        self::assertStringContainsString('not a valid local part', current($messages));
    }

    /**
     * Ensures that no validation failure message is produced when the local part follows the quoted-string format
     */
    public function testLocalPartQuotedString(): void
    {
        self::assertTrue($this->validator->isValid('"Some User"@example.com'));

        $messages = $this->validator->getMessages();

        self::assertIsArray($messages);
        self::assertCount(0, $messages);
    }

    /**
     * Ensures that validation fails when the hostname is invalid
     */
    public function testHostnameInvalid(): void
    {
        self::assertFalse($this->validator->isValid('username@ example . com'));

        $messages = $this->validator->getMessages();

        self::assertGreaterThanOrEqual(1, count($messages));
        self::assertStringContainsString('not a valid hostname', current($messages));
    }

    /**
     * Ensures that quoted-string local part is considered valid
     */
    public function testQuotedString(): void
    {
        $emailAddresses = [
            '""@domain.com', // Optional
            '" "@domain.com', // x20
            '"!"@domain.com', // x21
            '"\""@domain.com', // \" (escaped x22)
            '"#"@domain.com', // x23
            '"$"@domain.com', // x24
            '"Z"@domain.com', // x5A
            '"["@domain.com', // x5B
            '"\\\"@domain.com', // \\ (escaped x5C)
            '"]"@domain.com', // x5D
            '"^"@domain.com', // x5E
            '"}"@domain.com', // x7D
            '"~"@domain.com', // x7E
            '"username"@example.com',
            '"bob%jones"@domain.com',
            '"bob jones"@domain.com',
            '"bob@jones"@domain.com',
            '"[[ bob ]]"@domain.com',
            '"jones"@domain.com',
        ];

        foreach ($emailAddresses as $input) {
            self::assertTrue(
                $this->validator->isValid($input),
                "$input failed to pass validation:\n" . implode("\n", $this->validator->getMessages()),
            );
        }
    }

    /**
     * Ensures that quoted-string local part is considered invalid
     */
    public function testInvalidQuotedString(): void
    {
        $emailAddresses = [
            "\"\x00\"@example.com",
            "\"\x01\"@example.com",
            "\"\x1E\"@example.com",
            "\"\x1F\"@example.com",
            '"""@example.com', // x22 (not escaped)
            '"\"@example.com', // x5C (not escaped)
            "\"\x7F\"@example.com",
        ];

        foreach ($emailAddresses as $input) {
            self::assertFalse(
                $this->validator->isValid($input),
                "$input failed to pass validation:\n" . implode("\n", $this->validator->getMessages()),
            );
        }
    }

    /**
     * Ensures that validation fails when the e-mail is given as for display,
     * with angle brackets around the actual address
     */
    public function testEmailDisplay(): void
    {
        self::assertFalse($this->validator->isValid('User Name <username@example.com>'));

        $messages = $this->validator->getMessages();

        self::assertGreaterThanOrEqual(3, count($messages));
        self::assertStringContainsString('not a valid hostname', current($messages));
        self::assertStringContainsString('cannot match TLD', next($messages));
        self::assertStringContainsString('does not appear to be a valid local network name', next($messages));
    }

    /**
     * @psalm-return array<string, array{0: string}>
     */
    public static function validEmailAddresses(): array
    {
        // @codingStandardsIgnoreStart
        $return = [
            'bob@domain.com'                                                          => ['bob@domain.com'],
            'bob.jones@domain.co.uk'                                                  => ['bob.jones@domain.co.uk'],
            'bob.jones.smythe@domain.co.uk'                                           => ['bob.jones.smythe@domain.co.uk'],
            'BoB@domain.museum'                                                       => ['BoB@domain.museum'],
            'bobjones@domain.info'                                                    => ['bobjones@domain.info'],
            'bob+jones@domain.us'                                                     => ['bob+jones@domain.us'],
            'bob+jones@domain.co.uk'                                                  => ['bob+jones@domain.co.uk'],
            'bob@some.domain.uk.com'                                                  => ['bob@some.domain.uk.com'],
            'bob@verylongdomainsupercalifragilisticexpialidociousspoonfulofsugar.com' => ['bob@verylongdomainsupercalifragilisticexpialidociousspoonfulofsugar.com'],
            "B.O'Callaghan@domain.com"                                                => ["B.O'Callaghan@domain.com"],
        ];

        if (extension_loaded('intl')) {
            $return['иван@письмо.рф']          = ['иван@письмо.рф'];
            $return['öäü@ä-umlaut.de']         = ['öäü@ä-umlaut.de'];
            $return['frédéric@domain.com']     = ['frédéric@domain.com'];
            $return['bob@тест.рф']             = ['bob@тест.рф'];
            $return['bob@xn--e1aybc.xn--p1ai'] = ['bob@xn--e1aybc.xn--p1ai'];
        }

        return $return;
        // @codingStandardsIgnoreEnd
    }

    /**
     * Ensures that the validator follows expected behavior for valid email addresses
     */
    #[DataProvider('validEmailAddresses')]
    public function testBasicValid(string $value): void
    {
        self::assertTrue(
            $this->validator->isValid($value),
            sprintf(
                '%s failed validation: %s',
                $value,
                implode("\n", $this->validator->getMessages())
            )
        );
    }

    /**
     * @psalm-return array<string, array{0: string}>
     */
    public static function invalidEmailAddresses(): array
    {
        // @codingStandardsIgnoreStart
        return [
            '[empty]'                                                                  => [''],
            'bob jones@domain.com'                                                     => ['bob jones@domain.com'],
            '.bobJones@studio24.com'                                                   => ['.bobJones@studio24.com'],
            'bobJones.@studio24.com'                                                   => ['bobJones.@studio24.com'],
            'bob.Jones.@studio24.com'                                                  => ['bob.Jones.@studio24.com'],
            'bob@verylongdomainsupercalifragilisticexpialidociousaspoonfulofsugar.com' => ['bob@verylongdomainsupercalifragilisticexpialidociousaspoonfulofsugar.com'],
            'bob+domain.com'                                                           => ['bob+domain.com'],
            'bob.domain.com'                                                           => ['bob.domain.com'],
            'bob @domain.com'                                                          => ['bob @domain.com'],
            'bob@ domain.com'                                                          => ['bob@ domain.com'],
            'bob @ domain.com'                                                         => ['bob @ domain.com'],
            'Abc..123@example.com'                                                     => ['Abc..123@example.com'],
            '"bob%jones@domain.com'                                                    => ['"bob%jones@domain.com'],
            'multiline'                                                                => ['bob

            @domain.com'],
        ];
        // @codingStandardsIgnoreEnd
    }

    /**
     * Ensures that the validator follows expected behavior for invalid email addresses
     */
    #[DataProvider('invalidEmailAddresses')]
    public function testBasicInvalid(string $value): void
    {
        self::assertFalse($this->validator->isValid($value));
    }

    /**
     * Ensures that the validator follows expected behavior for valid email addresses with complex local parts
     */
    public function testComplexLocalValid(): void
    {
        $emailAddresses = [
            'Bob.Jones@domain.com',
            'Bob.Jones!@domain.com',
            'Bob&Jones@domain.com',
            '/Bob.Jones@domain.com',
            '#Bob.Jones@domain.com',
            'Bob.Jones?@domain.com',
            'Bob~Jones@domain.com',
        ];

        foreach ($emailAddresses as $input) {
            self::assertTrue($this->validator->isValid($input));
        }
    }

    /**
     * Ensures that the validator follows expected behavior for valid email addresses with the non-strict option
     */
    public function testNonStrict(): void
    {
        $validator      = new EmailAddress(['strict' => false]);
        $emailAddresses = [
            // RFC 5321 does mention a limit of 64 for the username,
            // but it also states "To the maximum extent possible,
            // implementation techniques that impose no limits on the
            // length of these objects should be used.".
            // http://tools.ietf.org/html/rfc5321#section-4.5.3.1
            'line length 320' => str_repeat('x', 309) . '@domain.com',
            'line length 321' => str_repeat('x', 310) . '@domain.com',
            'line length 911' => str_repeat('x', 900) . '@domain.com',
        ];

        foreach ($emailAddresses as $input) {
            self::assertTrue($validator->isValid($input));
        }
    }

    /** @return array<string, array{0: string, 1: bool}> */
    public static function emailAddressesForMxChecks(): array
    {
        return [
            'Bob.Jones@php.net'                => ['Bob.Jones@php.net', true],
            'Bob.Jones@zend.com'               => ['Bob.Jones@zend.com', true],
            'Bob.Jones@bad.example.com'        => ['Bob.Jones@bad.example.com', false],
            'Bob.Jones@anotherbad.example.com' => ['Bob.Jones@anotherbad.example.com', false],
        ];
    }

    /**
     * Ensures that the validator follows expected behavior for checking MX records
     */
    #[DataProvider('emailAddressesForMxChecks')]
    public function testMXRecords(string $emailAddress, bool $expect): void
    {
        $this->skipIfOnlineTestsDisabled();

        $validator = new EmailAddress([
            'allow'      => Hostname::ALLOW_DNS,
            'useMxCheck' => true,
        ]);

        // Are MX checks supported by this system?
        if (! $validator->isMxSupported()) {
            self::markTestSkipped('Testing MX records is not supported with this configuration');
        }

        self::assertSame($expect, $validator->isValid($emailAddress), implode("\n", $validator->getMessages()));

        // Try a check via setting the option via a method
        unset($validator);

        $validator = new EmailAddress();
        $validator->useMxCheck(true);
        self::assertSame($expect, $validator->isValid($emailAddress), implode("\n", $validator->getMessages()));
    }

    /**
     * Ensures that the validator follows expected behavior for checking MX records with A record fallback.
     * This behavior is documented in RFC 2821, section 5: "If no MX records are found, but an A RR is
     * found, the A RR is treated as if it was associated with an implicit MX RR, with a preference of 0,
     * pointing to that host.
     */
    public function testNoMxRecordARecordFallback(): void
    {
        $this->skipIfOnlineTestsDisabled();

        $validator = new EmailAddress([
            'allow'      => Hostname::ALLOW_DNS,
            'useMxCheck' => true,
        ]);

        // Are MX checks supported by this system?
        if (! $validator->isMxSupported()) {
            self::markTestSkipped('Testing MX records is not supported with this configuration');
        }

        $email = 'good@www.getlaminas.org';
        $host  = preg_replace('/.*@/', '', $email);

        //Assert that email host contains no MX records.
        self::assertFalse(checkdnsrr($host, 'MX'), 'Email host contains MX records');

        //Assert that email host contains at least one A record.
        self::assertTrue(checkdnsrr($host, 'A'), 'Email host contains no A records');

        //Assert that validator falls back to A record.
        self::assertTrue($validator->isValid($email), implode("\n", $validator->getMessages()));
    }

    /**
     * Test changing hostname settings via EmailAddress object
     */
    public function testHostnameSettings(): void
    {
        $validator = new EmailAddress();

        // Check no IDN matching
        $validator->getHostnameValidator()->useIdnCheck(false);
        $valuesExpected = [
            [false, ['name@b�rger.de', 'name@h�llo.de', 'name@h�llo.se']],
        ];

        foreach ($valuesExpected as $element) {
            foreach ($element[1] as $input) {
                self::assertSame($element[0], $validator->isValid($input), implode("\n", $validator->getMessages()));
            }
        }

        // Check no TLD matching
        $validator->getHostnameValidator()->useTldCheck(false);
        $valuesExpected = [
            [true, ['name@domain.xx', 'name@domain.zz', 'name@domain.madeup']],
        ];

        foreach ($valuesExpected as $element) {
            foreach ($element[1] as $input) {
                self::assertSame($element[0], $validator->isValid($input), implode("\n", $validator->getMessages()));
            }
        }
    }

    /**
     * Ensures that getMessages() returns expected default value (an empty array)
     */
    public function testGetMessages(): void
    {
        self::assertSame([], $this->validator->getMessages());
    }

    #[Group('Laminas-2861')]
    public function testHostnameValidatorMessagesShouldBeTranslated(): void
    {
        if (! extension_loaded('intl')) {
            self::markTestSkipped('ext/intl not enabled');
        }

        $hostnameValidator    = new Hostname();
        $translations         = [
            'hostnameIpAddressNotAllowed'   => 'hostnameIpAddressNotAllowed translation',
            'hostnameUnknownTld'            => 'The input appears to be a DNS hostname '
            . 'but cannot match TLD against known list',
            'hostnameDashCharacter'         => 'hostnameDashCharacter translation',
            'hostnameInvalidHostnameSchema' => 'hostnameInvalidHostnameSchema translation',
            'hostnameUndecipherableTld'     => 'hostnameUndecipherableTld translation',
            'hostnameInvalidHostname'       => 'hostnameInvalidHostname translation',
            'hostnameInvalidLocalName'      => 'hostnameInvalidLocalName translation',
            'hostnameLocalNameNotAllowed'   => 'hostnameLocalNameNotAllowed translation',
        ];
        $loader               = new TestAsset\ArrayTranslator();
        $loader->translations = $translations;
        $translator           = new TestAsset\Translator();
        $translator->getPluginManager()->setService('test', $loader);
        $translator->addTranslationFile('test', null);

        $this->validator->setTranslator($translator)->setHostnameValidator($hostnameValidator);

        $this->validator->isValid('_XX.!!3xx@0.239,512.777');
        $messages = $hostnameValidator->getMessages();
        $found    = false;
        foreach ($messages as $code => $message) {
            if (array_key_exists($code, $translations)) {
                self::assertSame($translations[$code], $message);

                $found = true;

                break;
            }
        }

        self::assertTrue($found);
    }

    #[Group('Laminas-4888')]
    public function testEmailsExceedingLength(): void
    {
        // @codingStandardsIgnoreStart
        $emailAddresses = [
            'thislocalpathoftheemailadressislongerthantheallowedsizeof64characters@domain.com',
            'bob@verylongdomainsupercalifragilisticexpialidociousspoonfulofsugarverylongdomainsupercalifragilisticexpialidociousspoonfulofsugarverylongdomainsupercalifragilisticexpialidociousspoonfulofsugarverylongdomainsupercalifragilisticexpialidociousspoonfulofsugarexpialidociousspoonfulofsugar.com',
        ];
        // @codingStandardsIgnoreEnd

        foreach ($emailAddresses as $input) {
            self::assertFalse($this->validator->isValid($input));
        }
    }

    #[Group('Laminas-4352')]
    public function testNonStringValidation(): void
    {
        self::assertFalse($this->validator->isValid([1 => 1]));
    }

    #[Group('Laminas-7490')]
    public function testSettingHostnameMessagesThroughEmailValidator(): void
    {
        $translations = [
            'hostnameIpAddressNotAllowed'   => 'hostnameIpAddressNotAllowed translation',
            'hostnameUnknownTld'            => 'hostnameUnknownTld translation',
            'hostnameDashCharacter'         => 'hostnameDashCharacter translation',
            'hostnameInvalidHostnameSchema' => 'hostnameInvalidHostnameSchema translation',
            'hostnameUndecipherableTld'     => 'hostnameUndecipherableTld translation',
            'hostnameInvalidHostname'       => 'hostnameInvalidHostname translation',
            'hostnameInvalidLocalName'      => 'hostnameInvalidLocalName translation',
            'hostnameLocalNameNotAllowed'   => 'hostnameLocalNameNotAllowed translation',
        ];

        $this->validator->setMessages($translations);
        $this->validator->isValid('_XX.!!3xx@0.239,512.777');
        $messages = $this->validator->getMessages();
        $found    = false;
        foreach ($messages as $code => $message) {
            if (array_key_exists($code, $translations)) {
                self::assertSame($translations[$code], $message);

                $found = true;

                break;
            }
        }

        self::assertTrue($found);
    }

    /**
     * Testing initializing with several options
     */
    public function testInstanceWithOldOptions(): void
    {
        $handler   = set_error_handler([$this, 'errorHandler'], E_USER_NOTICE);
        $validator = new EmailAddress();
        $options   = $validator->getOptions();

        self::assertSame(Hostname::ALLOW_DNS, $options['allow']);
        self::assertFalse($options['useMxCheck']);

        try {
            /** @psalm-suppress TooManyArguments */
            $validator = new EmailAddress(Hostname::ALLOW_ALL, true, new Hostname(Hostname::ALLOW_ALL));
            $options   = $validator->getOptions();

            self::assertSame(Hostname::ALLOW_ALL, $options['allow']);
            self::assertTrue($options['useMxCheck']);
            set_error_handler($handler);
        } catch (InvalidArgumentException) {
            self::markTestSkipped('MX not available on this system');
        }
    }

    /**
     * Testing setOptions
     */
    public function testSetOptions(): void
    {
        $this->validator->setOptions(['messages' => [EmailAddress::INVALID => 'TestMessage']]);
        $messages = $this->validator->getMessageTemplates();

        self::assertSame('TestMessage', $messages[EmailAddress::INVALID]);

        $oldHostname = $this->validator->getHostnameValidator();
        $this->validator->setOptions(['hostnameValidator' => new Hostname(Hostname::ALLOW_ALL)]);
        $hostname = $this->validator->getHostnameValidator();

        self::assertNotSame($oldHostname, $hostname);
    }

    /**
     * Testing setMessage
     */
    public function testSetSingleMessage(): void
    {
        $messages = $this->validator->getMessageTemplates();

        self::assertNotSame('TestMessage', $messages[EmailAddress::INVALID]);

        $this->validator->setMessage('TestMessage', EmailAddress::INVALID);
        $messages = $this->validator->getMessageTemplates();

        self::assertSame('TestMessage', $messages[EmailAddress::INVALID]);
    }

    public function testSetSingleMessageViaOptions(): void
    {
        $validator = new EmailAddress(['message' => 'TestMessage']);

        foreach ($validator->getMessageTemplates() as $message) {
            self::assertSame('TestMessage', $message);
        }

        foreach ($validator->getHostnameValidator()->getMessageTemplates() as $message) {
            self::assertSame('TestMessage', $message);
        }
    }

    public function testSetMultipleMessageViaOptions(): void
    {
        $validator = new EmailAddress(['messages' => [EmailAddress::INVALID => 'TestMessage']]);
        $messages  = $validator->getMessageTemplates();

        self::assertSame('TestMessage', $messages[EmailAddress::INVALID]);
    }

    /**
     * Testing getValidateMx
     */
    public function testGetValidateMx(): void
    {
        self::assertFalse($this->validator->getMxCheck());
    }

    /**
     * Testing getDeepMxCheck
     */
    public function testGetDeepMxCheck(): void
    {
        self::assertFalse($this->validator->getDeepMxCheck());
    }

    /**
     * Testing setMessage for all messages
     */
    #[Group('Laminas-10690')]
    public function testSetMultipleMessages(): void
    {
        $messages = $this->validator->getMessageTemplates();

        self::assertNotSame('TestMessage', $messages[EmailAddress::INVALID]);

        $this->validator->setMessage('TestMessage');

        foreach ($this->validator->getMessageTemplates() as $message) {
            self::assertSame('TestMessage', $message);
        }

        foreach ($this->validator->getHostnameValidator()->getMessageTemplates() as $message) {
            self::assertSame('TestMessage', $message);
        }
    }

    /**
     * Testing getDomainCheck
     */
    public function testGetDomainCheck(): void
    {
        self::assertTrue($this->validator->getDomainCheck());
    }

    public function errorHandler(int $errno, string $errstr): void
    {
    }

    #[Group('Laminas-11222')]
    #[Group('Laminas-11451')]
    public function testEmailAddressesWithTrailingDotInHostPartAreRejected(): void
    {
        self::assertFalse($this->validator->isValid('example@gmail.com.'));
        self::assertFalse($this->validator->isValid('test@test.co.'));
        self::assertFalse($this->validator->isValid('test@test.co.za.'));
    }

    #[Group('Laminas-11239')]
    public function testNotSetHostnameValidator(): void
    {
        $hostname = $this->validator->getHostnameValidator();

        self::assertInstanceOf(Hostname::class, $hostname);
    }

    public function testIsMxSupported(): void
    {
        $validator = new EmailAddress(['useMxCheck' => true, 'allow' => Hostname::ALLOW_ALL]);

        self::assertIsBool($validator->isMxSupported());
    }

    /**
     * Test getMXRecord
     */
    public function testGetMXRecord(): void
    {
        $this->skipIfOnlineTestsDisabled();

        $validator = new EmailAddress(['useMxCheck' => true, 'allow' => Hostname::ALLOW_ALL]);

        if (! $validator->isMxSupported()) {
            self::markTestSkipped('Testing MX records is not supported with this configuration');
        }

        self::assertTrue($validator->isValid('john.doe@gmail.com'));

        $result = $validator->getMXRecord();

        self::assertNotEmpty($result);
    }

    public function testEqualsMessageTemplates(): void
    {
        self::assertSame(
            [
                EmailAddress::INVALID,
                EmailAddress::INVALID_FORMAT,
                EmailAddress::INVALID_HOSTNAME,
                EmailAddress::INVALID_MX_RECORD,
                EmailAddress::INVALID_SEGMENT,
                EmailAddress::DOT_ATOM,
                EmailAddress::QUOTED_STRING,
                EmailAddress::INVALID_LOCAL_PART,
                EmailAddress::LENGTH_EXCEEDED,
            ],
            array_keys($this->validator->getMessageTemplates())
        );
        self::assertSame($this->validator->getOption('messageTemplates'), $this->validator->getMessageTemplates());
    }

    public function testEqualsMessageVariables(): void
    {
        $messageVariables = [
            'hostname'  => 'hostname',
            'localPart' => 'localPart',
        ];

        self::assertSame($messageVariables, $this->validator->getOption('messageVariables'));
        self::assertSame(array_keys($messageVariables), $this->validator->getMessageVariables());
    }

    #[Group('Laminas-130')]
    public function testUseMxCheckBasicValid(): void
    {
        $this->skipIfOnlineTestsDisabled();

        $validator = new EmailAddress([
            'useMxCheck'     => true,
            'useDeepMxCheck' => true,
        ]);

        $emailAddresses = [
            'bob@gmail.com',
            'bob.jones@bbc.co.uk',
            'bob.jones.smythe@bbc.co.uk',
            'BoB@aol.com',
            'bobjones@nist.gov',
            "B.O'Callaghan@usmc.mil",
            'bob+jones@php.net',
            'bob+jones@dailymail.co.uk',
            'bob@teaparty.uk.com',
            'bob@thelongestdomainnameintheworldandthensomeandthensomemoreandmore.com',
        ];

        if (extension_loaded('intl')) {
            $emailAddresses[] = 'test@кц.рф'; // Registry for .рф-TLD
            $emailAddresses[] = 'test@xn--j1ay.xn--p1ai';
        }

        foreach ($emailAddresses as $input) {
            self::assertTrue(
                $validator->isValid($input),
                "$input failed to pass validation:\n" . implode("\n", $validator->getMessages()),
            );
        }
    }

    #[Group('Laminas-130')]
    public function testUseMxRecordsBasicInvalid(): void
    {
        $validator = new EmailAddress([
            'useMxCheck'     => true,
            'useDeepMxCheck' => true,
        ]);

        $emailAddresses = [
            '',
            'bob

            @domain.com',
            'bob jones@domain.com',
            '.bobJones@studio24.com',
            'bobJones.@studio24.com',
            'bob.Jones.@studio24.com',
            '"bob%jones@domain.com',
            'bob@verylongdomainsupercalifragilisticexpialidociousaspoonfulofsugar.com',
            'bob+domain.com',
            'bob.domain.com',
            'bob @domain.com',
            'bob@ domain.com',
            'bob @ domain.com',
            'Abc..123@example.com',
        ];

        if (! extension_loaded('intl')) {
            $emailAddresses[] = 'иван@письмо.рф';
            $emailAddresses[] = 'xn--@-7sbfxdyelgv5j.xn--p1ai';
        }

        foreach ($emailAddresses as $input) {
            self::assertFalse($validator->isValid($input), implode("\n", $this->validator->getMessages()) . $input);
        }
    }

    #[Group('Laminas-12349')]
    public function testReservedIpRangeValidation(): void
    {
        $validator = new TestAsset\EmailValidatorWithExposedIsReserved();

        // 0.0.0.0/8
        self::assertTrue($validator->isReserved('0.0.0.0'));
        self::assertTrue($validator->isReserved('0.255.255.255'));

        // 10.0.0.0/8
        self::assertTrue($validator->isReserved('10.0.0.0'));
        self::assertTrue($validator->isReserved('10.255.255.255'));

        // 127.0.0.0/8
        self::assertTrue($validator->isReserved('127.0.0.0'));
        self::assertTrue($validator->isReserved('127.255.255.255'));

        // 100.64.0.0/10
        self::assertTrue($validator->isReserved('100.64.0.0'));
        self::assertTrue($validator->isReserved('100.127.255.255'));

        // 172.16.0.0/12
        self::assertTrue($validator->isReserved('172.16.0.0'));
        self::assertTrue($validator->isReserved('172.31.255.255'));

        // 198.18.0.0./15
        self::assertTrue($validator->isReserved('198.18.0.0'));
        self::assertTrue($validator->isReserved('198.19.255.255'));

        // 169.254.0.0/16
        self::assertTrue($validator->isReserved('169.254.0.0'));
        self::assertTrue($validator->isReserved('169.254.255.255'));

        // 192.168.0.0/16
        self::assertTrue($validator->isReserved('192.168.0.0'));
        self::assertTrue($validator->isReserved('192.168.255.25'));

        // 192.0.2.0/24
        self::assertTrue($validator->isReserved('192.0.2.0'));
        self::assertTrue($validator->isReserved('192.0.2.255'));

        // 192.88.99.0/24
        self::assertTrue($validator->isReserved('192.88.99.0'));
        self::assertTrue($validator->isReserved('192.88.99.255'));

        // 198.51.100.0/24
        self::assertTrue($validator->isReserved('198.51.100.0'));
        self::assertTrue($validator->isReserved('198.51.100.255'));

        // 203.0.113.0/24
        self::assertTrue($validator->isReserved('203.0.113.0'));
        self::assertTrue($validator->isReserved('203.0.113.255'));

        // 224.0.0.0/4
        self::assertTrue($validator->isReserved('224.0.0.0'));
        self::assertTrue($validator->isReserved('239.255.255.255'));

        // 240.0.0.0/4
        self::assertTrue($validator->isReserved('240.0.0.0'));
        self::assertTrue($validator->isReserved('255.255.255.254'));

        // 255.255.255.255/32
        self::assertTrue($validator->isReserved('255.255.55.255'));
    }

    #[Group('Laminas-12349')]
    public function testIpRangeValidationOnRangesNoLongerMarkedAsReserved(): void
    {
        $validator = new TestAsset\EmailValidatorWithExposedIsReserved();

        // 128.0.0.0/16
        self::assertFalse($validator->isReserved('128.0.0.0'));
        self::assertFalse($validator->isReserved('128.0.255.255'));

        // 191.255.0.0/16
        self::assertFalse($validator->isReserved('191.255.0.0'));
        self::assertFalse($validator->isReserved('191.255.255.255'));

        // 223.255.255.0/24
        self::assertFalse($validator->isReserved('223.255.255.0'));
        self::assertFalse($validator->isReserved('223.255.255.255'));
    }

    private function skipIfOnlineTestsDisabled(): void
    {
        $enabled = getenv('TESTS_LAMINAS_VALIDATOR_ONLINE_ENABLED');
        if ($enabled === false || $enabled === '') {
            self::markTestSkipped('Testing MX records has been disabled');
        }
    }

    public function testCanSetDomainCheckFlag(): void
    {
        $validator = new EmailAddress();
        $validator->useDomainCheck(false);

        self::assertFalse($validator->getDomainCheck());
    }

    public function testWillNotCheckEmptyDeepMxChecks(): void
    {
        $validator = new EmailAddress([
            'useMxCheck'     => true,
            'useDeepMxCheck' => true,
        ]);

        self::assertFalse($validator->isValid('jon@example.com'));
    }
}
