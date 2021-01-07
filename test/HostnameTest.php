<?php

/**
 * @see       https://github.com/laminas/laminas-validator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-validator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-validator/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Validator;

use Laminas\Validator\Hostname;
use PHPUnit\Framework\TestCase;

/**
 * @group      Laminas_Validator
 */
class HostnameTest extends TestCase
{
    /**
     * Default instance created for all test methods
     *
     * @var Hostname
     */
    protected $validator;

    /**
     * @var string
     */
    protected $origEncoding;

    protected function setUp() : void
    {
        $this->origEncoding = ini_get('default_charset');
        $this->validator    = new Hostname();
    }

    /**
     * Reset iconv
     */
    protected function tearDown() : void
    {
        ini_set('default_charset', $this->origEncoding);
    }

    /**
     * Ensures that the validator follows expected behavior
     *
     * @dataProvider basicDataProvider
     * @param int $option
     * @param bool $expected
     * @param string $hostname
     * @return void
     */
    public function testBasic(int $option, bool $expected, string $hostname)
    {
        $validator = new Hostname($option);
        self::assertSame($expected, $validator->isValid($hostname));
    }

    public function basicDataProvider() : array
    {
        return [
            'allow-ip succeeds for 1.2.3.4' => [Hostname::ALLOW_IP, true, '1.2.3.4'],
            'allow-ip succeeds for 10.0.0.1' => [Hostname::ALLOW_IP, true, '10.0.0.1'],
            'allow-ip succeeds for 255.255.255.255' => [Hostname::ALLOW_IP, true, '255.255.255.255'],
            'allow-ip fails for 1.2.3.4.5' => [Hostname::ALLOW_IP, false, '1.2.3.4.5'],
            'allow-ip fails for 0.0.0.256' => [Hostname::ALLOW_IP, false, '0.0.0.256'],
            'allow-dns succeeds for example.com' => [Hostname::ALLOW_DNS, true, 'example.com'],
            'allow-dns succeeds for example.museum' => [Hostname::ALLOW_DNS, true, 'example.museum'],
            'allow-dns succeeds for d.hatena.ne.jp' => [Hostname::ALLOW_DNS, true, 'd.hatena.ne.jp'],
            'allow-dns succeeds for example.photography' => [Hostname::ALLOW_DNS, true, 'example.photography'],
            'allow-dns fails for localhost' => [Hostname::ALLOW_DNS, false, 'localhost'],
            'allow-dns fails for localhost.localdomain' => [Hostname::ALLOW_DNS, false, 'localhost.localdomain'],
            'allow-dns fails for 1.2.3.4' => [Hostname::ALLOW_DNS, false, '1.2.3.4'],
            'allow-dns fails for domain.invalid' => [Hostname::ALLOW_DNS, false, 'domain.invalid'],
            'allow-local succeeds for localhost' => [Hostname::ALLOW_LOCAL, true, 'localhost'],
            'allow-local succeeds for localhost.localdomain' => [Hostname::ALLOW_LOCAL, true, 'localhost.localdomain'],
            'allow-local succeeds for example.com' => [Hostname::ALLOW_LOCAL, true, 'example.com'],
            'allow-all succeeds for localhost' => [Hostname::ALLOW_ALL, true, 'localhost'],
            'allow-all succeeds for example.com' => [Hostname::ALLOW_ALL, true, 'example.com'],
            'allow-all succeeds for 1.2.3.4' => [Hostname::ALLOW_ALL, true, '1.2.3.4'],
            'allow-local fails for local host' => [Hostname::ALLOW_LOCAL, false, 'local host'],
            'allow-local fails for example.com' => [Hostname::ALLOW_LOCAL, false, 'example,com'],
            'allow-local fails for exam_ple.com' => [Hostname::ALLOW_LOCAL, false, 'exam_ple.com'],
        ];
    }

    /**
     * @dataProvider combinationDataProvider
     * @param int $option
     * @param bool $expected
     * @param string $hostname
     * @return void
     */
    public function testCombination(int $option, bool $expected, string $hostname)
    {
        $validator = new Hostname($option);
        self::assertSame($expected, $validator->isValid($hostname));
    }

    public function combinationDataProvider() : array
    {
        return [
            'dns or local succeeds for domain.com' => [Hostname::ALLOW_DNS | Hostname::ALLOW_LOCAL, true, 'domain.com'],
            'dns or local succeeds for localhost' => [Hostname::ALLOW_DNS | Hostname::ALLOW_LOCAL, true, 'localhost'],
            'dns or local succeeds for local.localhost' => [Hostname::ALLOW_DNS | Hostname::ALLOW_LOCAL, true, 'local.localhost'],
            'dns or local fails for 1.2.3.4' => [Hostname::ALLOW_DNS | Hostname::ALLOW_LOCAL, false, '1.2.3.4'],
            'dns or local fails for 255.255.255.255' => [Hostname::ALLOW_DNS | Hostname::ALLOW_LOCAL, false, '255.255.255.255'],
            'dns or ip succeeds for 1.2.3.4' => [Hostname::ALLOW_DNS | Hostname::ALLOW_IP, true, '1.2.3.4'],
            'dns or ip succeeds for 255.255.255.255' => [Hostname::ALLOW_DNS | Hostname::ALLOW_IP, true, '255.255.255.255'],
            'dns or ip fails for localhost' => [Hostname::ALLOW_DNS | Hostname::ALLOW_IP, false, 'localhost'],
            'dns or ip fails for local.localhost' => [Hostname::ALLOW_DNS | Hostname::ALLOW_IP, false, 'local.localhost'],
        ];
    }

    /**
     * Ensure the dash character tests work as expected
     *
     * @dataProvider dashesDataProvider
     *
     * @param int $option
     * @param bool $expected
     * @param string $hostname
     *
     * @return void
     */
    public function testDashes(int $option, bool $expected, string $hostname): void
    {
        $validator = new Hostname($option);
        self::assertSame($expected, $validator->isValid($hostname));
    }

    public function dashesDataProvider() : array
    {
        return [
            'allow-dns succeeds for domain.com' => [Hostname::ALLOW_DNS, true, 'domain.com'],
            'allow-dns succeeds for doma-in.com' => [Hostname::ALLOW_DNS, true, 'doma-in.com'],
            'allow-dns fails for -domain.com' => [Hostname::ALLOW_DNS, false, '-domain.com'],
            'allow-dns fails for domain-.com' => [Hostname::ALLOW_DNS, false, 'domain-.com'],
            'allow-dns fails for do--main.com' => [Hostname::ALLOW_DNS, false, 'do--main.com'],
            'allow-dns fails for do-main-.com' => [Hostname::ALLOW_DNS, false, 'do-main-.com'],
        ];
    }

    /**
     * Ensure the underscore character tests work as expected
     *
     * @dataProvider domainsWithUnderscores
     *
     * @param string $input
     * @param bool $expected
     *
     * @return void
     */
    public function testValidatorHandlesUnderscoresInDomainsCorrectly(string $input, bool $expected): void
    {
        $validator = new Hostname(Hostname::ALLOW_DNS);
        self::assertSame($expected, $validator->isValid($input), implode("\n", $validator->getMessages()));
    }

    public function domainsWithUnderscores() : array
    {
        return [
            'subdomain with leading underscore' => ['_subdomain.domain.com', true,],
            'subdomain with trailing underscore' => ['subdomain_.domain.com', true,],
            'subdomain with single underscore' => ['sub_domain.domain.com', true,],
            'subdomain with double underscore' => ['sub__domain.domain.com', true,],
            'root domain with leading underscore' => ['_domain.com', false,],
            'root domain with trailing underscore' => ['domain_.com', false,],
            'root domain with underscore' => ['do_main.com', false,],
        ];
    }

    /**
     * Ensure the underscore character tests work as expected when not using tld check
     *
     * @dataProvider domainsWithUnderscores
     *
     * @param string $input
     * @param bool $expected
     *
     * @return void
     */
    public function testValidatorHandlesUnderscoresInDomainsWithoutTldCheckCorrectly(string $input, bool $expected): void
    {
        $validator = new Hostname([
            'useTldCheck' => false,
            'allow' => Hostname::ALLOW_DNS,
        ]);
        self::assertSame($expected, $validator->isValid($input), implode("\n", $validator->getMessages()));
    }

    /**
     * Ensures that getMessages() returns expected default value
     *
     * @return void
     */
    public function testGetMessages()
    {
        self::assertSame([], $this->validator->getMessages());
    }

    /**
     * @dataProvider idnMatchingDataProvider
     *
     * @param string $input
     * @param bool $expected
     *
     * @return void
     */
    public function testIdnMatching(string $input, bool $expected): void
    {
        $validator = new Hostname();
        self::assertSame($expected, $validator->isValid($input));
    }

    public function idnMatchingDataProvider() : array
    {
        return [
            ['bürger.de', true],
            ['bÜrger.de', true],
            ['hãllo.de', true],
            ['hãllo.de', true],
            ['hållo.se', true],
            ['hÅllo.se', true],

            ['bürger.com', true],
            ['bÜrger.com', true],
            ['hãllo.com', true],
            ['hÃllo.com', true],
            ['hållo.com', true],
            ['hÅllo.com', true],
            ['plekitööd.ee', true],
            ['plekitÖÖd.ee', true],

            ['hãllo.lt', false],
            ['bürger.lt', false],
            ['hãllo.lt', false],

            ['hãllo.se', false],
            ['bürger.lt', false],
            ['hãllo.uk', false],
        ];
    }

    /**
     * @dataProvider idnNoMatchingDataProvider
     *
     * @param string $input
     *
     * @return void
     */
    public function testIdnNoMatching(string $input): void
    {
        $validator = new Hostname();
        $validator->useIdnCheck(false);
        self::assertFalse($validator->isValid($input));
    }

    /**
     * Check setting no IDN matching via constructor
     *
     * @dataProvider idnNoMatchingDataProvider
     *
     * @return void
     */
    public function testIdnNoMatchingOptionConstructor($input): void
    {
        $validator = new Hostname(Hostname::ALLOW_DNS, false);
        self::assertFalse($validator->isValid($input));
    }

    public function idnNoMatchingDataProvider() : array
    {
        return [
            ['bürger.de'],
            ['hãllo.de'],
            ['hållo.se'],
            ['bürger.com'],
            ['hãllo.com'],
            ['hållo.com'],
        ];
    }

    /**
     * @dataProvider tldMatchingDataProvider
     *
     * @param string $input
     * @param bool $expected
     *
     * @return void
     */
    public function testTldMatching(string $input, bool $expected): void
    {
        $validator = new Hostname();
        self::assertSame($expected, $validator->isValid($input));
    }

    public function tldMatchingDataProvider() : array
    {
        return [
            ['domain.co.uk', true],
            ['domain.uk.com', true],
            ['domain.tl', true],
            ['domain.zw', true],

            ['domain.xx', false],
            ['domain.zz', false],
            ['domain.madeup', false],
        ];
    }

    /**
     * @dataProvider tldNoMatchingDataProvider
     *
     * @param string $input
     *
     * @return void
     */
    public function testTldNoMatching(string $input): void
    {
        $validator = new Hostname();
        $validator->useTldCheck(false);
        self::assertTrue($validator->isValid($input));
    }

    /**
     * Check setting no TLD matching via constructor
     *
     * @dataProvider tldNoMatchingDataProvider
     *
     * @param string $input
     *
     * @return void
     */
    public function testTldNoMatchingOptionConstructor(string $input): void
    {
        $validator = new Hostname(Hostname::ALLOW_DNS, true, false);
        self::assertTrue($validator->isValid($input));
    }

    public function tldNoMatchingDataProvider() : array
    {
        return [
            ['domain.xx'],
            ['domain.zz'],
            ['domain.madeup'],
        ];
    }

    /**
     * Ensures that getAllow() returns expected default value
     *
     * @return void
     */
    public function testGetAllow()
    {
        self::assertSame(Hostname::ALLOW_DNS, $this->validator->getAllow());
    }

    /**
     * Test changed with Laminas-6676, as IP check is only involved when IP patterns match
     *
     * @group Laminas-2861
     * @group Laminas-6676
     *
     * @return void
     */
    public function testValidatorMessagesShouldBeTranslated(): void
    {
        if (! extension_loaded('intl')) {
            $this->markTestSkipped('ext/intl not enabled');
        }

        $translations = [
            'hostnameInvalidLocalName' => 'The input does not appear to be a valid local network name',
        ];
        $loader = new TestAsset\ArrayTranslator();
        $loader->translations = $translations;
        $translator = new TestAsset\Translator();
        $translator->getPluginManager()->setService('default', $loader);
        $translator->addTranslationFile('default', null);
        $this->validator->setTranslator($translator);

        $this->validator->isValid('0.239,512.777');
        $messages = $this->validator->getMessages();
        $found = false;
        foreach ($messages as $code => $message) {
            if (array_key_exists($code, $translations)) {
                $found = true;
                break;
            }
        }

        self::assertTrue($found);
        self::assertSame($translations[$code], $message);
    }

    /**
     * @group Laminas-6033
     *
     * @dataProvider numberNamesDataProvider
     *
     * @return void
     */
    public function testNumberNames($input, $expected): void
    {
        $validator = new Hostname();
        self::assertSame($expected, $validator->isValid($input));
    }

    /**
     * @psalm-return array<array-key, array{0: string, 1: bool}>
     */
    public function numberNamesDataProvider(): array
    {
        return [
            ['www.danger1.com', true],
            ['danger.com', true],
            ['www.danger.com', true],

            ['www.danger1com', false],
            ['dangercom', false],
            ['www.dangercom', false],
        ];
    }

    /**
     * @group Laminas-6133
     *
     * @dataProvider punyCodeDecodingDataProvider
     *
     * @return void
     */
    public function testPunycodeDecoding($input, $expected): void
    {
        $validator = new Hostname();
        self::assertSame($expected, $validator->isValid($input));
    }

    /**
     * @psalm-return array<array-key, array{0: string, 1: bool}>
     */
    public function punyCodeDecodingDataProvider(): array
    {
        return [
            ['xn--brger-kva.com', true],
            ['xn--eckwd4c7cu47r2wf.jp', true],

            ['xn--brger-x45d2va.com', false],
            ['xn--bürger.com', false],
            ['xn--', false],
        ];
    }

    /**
     * @Laminas-4352
     *
     * @return void
     */
    public function testNonStringValidation(): void
    {
        self::assertFalse($this->validator->isValid([1 => 1]));
    }

    /**
     * @Laminas-7323
     *
     * @return void
     */
    public function testLatinSpecialChars(): void
    {
        self::assertFalse($this->validator->isValid('place@yah&oo.com'));
        self::assertFalse($this->validator->isValid('place@y*ahoo.com'));
        self::assertFalse($this->validator->isValid('ya#hoo'));
    }

    /**
     * @group Laminas-7277
     *
     * @dataProvider differentIconvEncodingDataProvider
     *
     * @return void
     */
    public function testDifferentIconvEncoding($input, $expected): void
    {
        ini_set('default_charset', 'ISO8859-1');
        $validator = new Hostname();

        self::assertSame($expected, $validator->isValid($input));
    }

    /**
     * @psalm-return array<array-key, array{0: string, 1: bool}>
     */
    public function differentIconvEncodingDataProvider(): array
    {
        return [
            ['bürger.com', true],
            ['bÜrger.com', true],
            ['hãllo.com', true],
            ['hÃllo.com', true],
            ['hållo.com', true],
            ['hÅllo.com', true],

            ['hãllo.lt', false],
            ['bürger.lt', false],
            ['hãllo.lt', false],
        ];
    }

    /**
     * @Laminas-8312
     *
     * @return void
     */
    public function testInvalidDoubledIdn(): void
    {
        self::assertFalse($this->validator->isValid('test.com / http://www.test.com'));
    }

    /**
     * @group Laminas-10267
     *
     * @dataProvider uriDataProvider
     *
     * @return void
     */
    public function testURI($input, $expected): void
    {
        $validator = new Hostname(Hostname::ALLOW_URI);
        self::assertSame($expected, $validator->isValid($input));
    }

    /**
     * @psalm-return array<array-key, array{0: string, 1: bool}>
     */
    public function uriDataProvider(): array
    {
        return [
            ['localhost', true],
            ['example.com', true],
            ['~ex%20ample', true],

            ['§bad', false],
            ['don?t.know', false],

            // @codingStandardsIgnoreStart
            ['thisisaverylonghostnamewhichextendstwohundredfiftysixcharactersandthereforshouldnotbeallowedbythisvalidatorbecauserfc3986limitstheallowedcharacterstoalimitoftwohunderedfiftysixcharactersinsumbutifthistestwouldfailthenitshouldreturntruewhichthrowsanexceptionbytheunittest', false],
            // @codingStandardsIgnoreEnd
        ];
    }

    /**
     * Ensure that a trailing "." in a local hostname is permitted
     *
     * @group Laminas-6363
     *
     * @dataProvider trailingDotDataProvider
     *
     * @param int $option
     * @param bool $expected
     * @param string $hostname
     *
     * @return void
     */
    public function testTrailingDot(int $option, bool $expected, string $hostname): void
    {
        $validator = new Hostname($option);
        self::assertSame($expected, $validator->isValid($hostname));
    }

    public function trailingDotDataProvider() : array
    {
        return [
            'allow-all succeeds for example.' => [Hostname::ALLOW_ALL, true, 'example.'],
            'allow-all succeeds for example.com.' => [Hostname::ALLOW_ALL, true, 'example.com.'],
            'allow-all succeeds for ~ex%20ample.' => [Hostname::ALLOW_ALL, true, '~ex%20ample.'],
            'allow-all fails for example..' => [Hostname::ALLOW_ALL, false, 'example..'],
            'allow-all succeeds for 1.2.3.4.' => [Hostname::ALLOW_ALL, true, '1.2.3.4.'],
            'allow-dns fails for example..' => [Hostname::ALLOW_DNS, false, 'example..'],
            'allow-dns fails for ~ex%20ample..' => [Hostname::ALLOW_DNS, false, '~ex%20ample..'],
            'allow-local succeeds for example.' => [Hostname::ALLOW_LOCAL, true, 'example.'],
            'allow-local succeeds for example.com.' => [Hostname::ALLOW_LOCAL, true, 'example.com.'],
        ];
    }

    /**
     * @group Laminas-11334
     *
     * @return void
     */
    public function testSupportsIpv6AddressesWhichContainHexDigitF(): void
    {
        $validator = new Hostname(Hostname::ALLOW_ALL);

        self::assertTrue($validator->isValid('FEDC:BA98:7654:3210:FEDC:BA98:7654:3210'));
        self::assertTrue($validator->isValid('1080:0:0:0:8:800:200C:417A'));
        self::assertTrue($validator->isValid('3ffe:2a00:100:7031::1'));
        self::assertTrue($validator->isValid('1080::8:800:200C:417A'));
        self::assertTrue($validator->isValid('::192.9.5.5'));
        self::assertTrue($validator->isValid('::FFFF:129.144.52.38'));
        self::assertTrue($validator->isValid('2010:836B:4179::836B:4179'));
    }

    /**
     * Test extended greek charset
     *
     * @group Laminas-11751
     *
     * @return void
     */
    public function testExtendedGreek(): void
    {
        $validator = new Hostname(Hostname::ALLOW_ALL);
        self::assertSame(true, $validator->isValid('ῆὧὰῧῲ.com'));
    }

    /**
     * @group Laminas-11796
     *
     * @return void
     */
    public function testIDNSI(): void
    {
        $validator = new Hostname(Hostname::ALLOW_ALL);

        self::assertTrue($validator->isValid('Test123.si'));
        self::assertTrue($validator->isValid('țest123.si'));
        self::assertTrue($validator->isValid('tĕst123.si'));
        self::assertTrue($validator->isValid('tàrø.si'));
        self::assertFalse($validator->isValid('رات.si'));
    }

    /**
     * @group Issue #5894 - Add .il IDN domain checking; add new TLDs
     *
     * @dataProvider idnilDataProvider
     *
     * @return void
     */
    public function testIDNIL($input, $expected): void
    {
        $validator = new Hostname(Hostname::ALLOW_ALL);
        self::assertSame($expected, $validator->isValid($input));
    }

    /**
     * @psalm-return array<array-key, array{0: string, 1: bool}>
     */
    public function idnilDataProvider(): array
    {
        return [
            ['xn----zhcbgfhe2aacg8fb5i.org.il', true],
            ['מבחן.il', true],
            ['מבחן123.il', true],

            ['tבדיקה123.il', false],
            ['رات.il', false],
        ];
    }

    /**
     * Ensures that the validator follows expected behavior for UTF-8 and Punycoded (ACE) TLDs
     *
     * @dataProvider validTLDHostnames
     *
     * @return void
     */
    public function testValidTLDHostnames($value): void
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
    public function validTLDHostnames(): array
    {
        // @codingStandardsIgnoreStart
        return [
            'ASCII label + UTF-8 TLD'                    => ['test123.онлайн'],
            'ASCII label + Punycoded TLD'                => ['test123.xn--80asehdb'],
            'UTF-8 label + UTF-8 TLD (cyrillic)'         => ['тест.рф'],
            'Punycoded label + Punycoded TLD (cyrillic)' => ['xn--e1aybc.xn--p1ai'],
        ];
        // @codingStandardsIgnoreEnd
    }

    /**
     * Ensures that the validator follows expected behavior for invalid UTF-8 and Punycoded (ACE) TLDs
     *
     * @dataProvider invalidTLDHostnames
     *
     * @return void
     */
    public function testInalidTLDHostnames($value): void
    {
        self::assertFalse($this->validator->isValid($value));
    }

    /**
     * @psalm-return array<string, array{0: string}>
     */
    public function invalidTLDHostnames(): array
    {
        // @codingStandardsIgnoreStart
        return [
            'Invalid mix of UTF-8 and ASCII in label'                              => ['சோதனை3.இலங்கை'],
            'Invalid mix of UTF-8 and ASCII in label (Punycoded)'                  => ['xn--3-owe4au9mpa.xn--xkc2al3hye2a'],
            'Invalid use of non-cyrillic characters with cyrillic TLD'             => ['رات.мон'],
            'Invalid use of non-cyrillic characters with cyrillic TLD (Punycoded)' => ['xn--mgbgt.xn--l1acc'],
        ];
        // @codingStandardsIgnoreEnd
    }

    public function testIDNIT(): void
    {
        $validator = new Hostname(Hostname::ALLOW_ALL);

        self::assertTrue($validator->isValid('plainascii.it'));
        self::assertTrue($validator->isValid('città-caffè.it'));
        self::assertTrue($validator->isValid('edgetest-àâäèéêëìîïòôöùûüæœçÿß.it'));
        self::assertFalse($validator->isValid('رات.it'));
    }

    public function testEqualsMessageTemplates(): void
    {
        $validator = $this->validator;
        $this->assertObjectHasAttribute('messageTemplates', $validator);
        $this->assertEquals($validator->getOption('messageTemplates'), $validator->getMessageTemplates());
    }

    public function testEqualsMessageVariables(): void
    {
        $validator = $this->validator;
        $this->assertObjectHasAttribute('messageVariables', $validator);
        $this->assertEquals(array_keys($validator->getOption('messageVariables')), $validator->getMessageVariables());
    }

    public function testHostnameWithOnlyIpChars(): void
    {
        $validator = new Hostname();
        self::assertTrue($validator->isValid('cafecafe.de'));
    }

    public function testValidCnHostname(): void
    {
        $validator = new Hostname();
        self::assertTrue($validator->isValid('google.cn'));
    }

    public function testValidBizHostname(): void
    {
        $validator = new Hostname();
        self::assertTrue($validator->isValid('google.biz'));
    }

    public function testInValidHostnameWithAt(): void
    {
        $validator = new Hostname();
        $this->assertFalse($validator->isValid('tapi4457@hsoqvf.biz'));
    }

    public function testHostnameWithEmptyDomainPart(): void
    {
        $validator = new Hostname();
        self::assertFalse($validator->isValid('.com'));
    }
}
