<?php

declare(strict_types=1);

namespace LaminasTest\Validator\Sitemap;

use Laminas\Validator\Sitemap\Changefreq;
use PHPUnit\Framework\TestCase;
use stdClass;

use function current;

/**
 * @group Laminas_Validator
 * @covers \Laminas\Validator\Sitemap\Changefreq
 */
final class ChangefreqTest extends TestCase
{
    private Changefreq $validator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = new Changefreq();
    }

    /**
     * Tests valid change frequencies
     */
    public function testValidChangefreqs(): void
    {
        $values = [
            'always',
            'hourly',
            'daily',
            'weekly',
            'monthly',
            'yearly',
            'never',
        ];

        foreach ($values as $value) {
            self::assertTrue($this->validator->isValid($value));
        }
    }

    /**
     * Tests strings that should be invalid
     */
    public function testInvalidStrings(): void
    {
        $values = [
            'alwayz',
            '_hourly',
            'Daily',
            'wEekly',
            'mönthly ',
            ' yearly ',
            'never ',
            'rofl',
            'yesterday',
        ];

        foreach ($values as $value) {
            self::assertFalse($this->validator->isValid($value));

            $messages = $this->validator->getMessages();

            self::assertStringContainsString('is not a valid', current($messages));
        }
    }

    /**
     * Tests values that are not strings
     */
    public function testNotString(): void
    {
        $values = [
            1,
            1.4,
            null,
            new stdClass(),
            true,
            false,
        ];

        foreach ($values as $value) {
            self::assertFalse($this->validator->isValid($value));

            $messages = $this->validator->getMessages();

            self::assertStringContainsString('String expected', current($messages));
        }
    }
}
