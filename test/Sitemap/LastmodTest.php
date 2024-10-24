<?php

declare(strict_types=1);

namespace LaminasTest\Validator\Sitemap;

use Laminas\Validator\Sitemap\Lastmod;
use PHPUnit\Framework\TestCase;
use stdClass;

use function current;

final class LastmodTest extends TestCase
{
    private Lastmod $validator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = new Lastmod();
    }

    /**
     * Tests valid change frequencies
     */
    public function testValidLastModTimes(): void
    {
        $values = [
            '1994-05-11T18:00:09-08:45',
            '1997-05-11T18:50:09+00:00',
            '1998-06-11T01:00:09-02:00',
            '1999-11-11T22:23:52+02:00',
            '1999-11-11T22:23+02:00',
            '2000-06-11',
            '2001-04-14',
            '2003-01-13',
            '2005-01-01',
            '2006-03-19',
            '2007-08-31',
            '2007-08-25',
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
            '1995-05-11T18:60:09-08:45',
            '1996-05-11T18:50:09+25:00',
            '2002-13-11',
            '2004-00-01',
            '2006-01-01\n',
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
