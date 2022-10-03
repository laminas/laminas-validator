<?php

declare(strict_types=1);

namespace LaminasTest\Validator\Sitemap;

use Laminas\Validator\Sitemap\Priority;
use PHPUnit\Framework\TestCase;
use stdClass;

use function current;

/**
 * @group Laminas_Validator
 * @covers \Laminas\Validator\Sitemap\Priority
 */
final class PriorityTest extends TestCase
{
    private Priority $validator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = new Priority();
    }

    /**
     * Tests valid priorities
     */
    public function testValidPriorities(): void
    {
        $values = [
            '0.0',
            '0.1',
            '0.2',
            '0.3',
            '0.4',
            '0.5',
            '0.6',
            '0.7',
            '0.8',
            '0.9',
            '1.0',
            '0.99',
            0.1,
            0.6667,
            0.0001,
            0.4,
            0,
            1,
            .35,
        ];

        foreach ($values as $value) {
            self::assertTrue($this->validator->isValid($value));
        }
    }

    /**
     * Tests invalid priorities
     */
    public function testInvalidPriorities(): void
    {
        $values = [
            -1,
            -0.1,
            1.1,
            100,
            10,
            2,
            '3',
            '-4',
        ];

        foreach ($values as $value) {
            self::assertFalse($this->validator->isValid($value));

            $messages = $this->validator->getMessages();

            self::assertStringContainsString('is not a valid', current($messages));
        }
    }

    /**
     * Tests values that are no numbers
     */
    public function testNotNumbers(): void
    {
        $values = [
            null,
            new stdClass(),
            true,
            false,
            'abcd',
        ];

        foreach ($values as $value) {
            self::assertFalse($this->validator->isValid($value));

            $messages = $this->validator->getMessages();

            self::assertStringContainsString('integer or float expected', current($messages));
        }
    }
}
