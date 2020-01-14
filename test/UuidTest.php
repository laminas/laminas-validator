<?php

/**
 * @see       https://github.com/laminas/laminas-validator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-validator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-validator/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Validator;

use Laminas\Validator\Uuid;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * Class UuidTest.
 *
 * Uuid test cases based on https://github.com/beberlei/assert/blob/master/tests/Assert/Tests/AssertTest.php
 */
final class UuidTest extends TestCase
{
    /**
     * @var Uuid
     */
    protected $validator;

    protected function setUp() : void
    {
        $this->validator = new Uuid();
    }

    /**
     * @param $uuid
     * @dataProvider validUuidProvider
     */
    public function testValidUuid($uuid)
    {
        $this->assertTrue($this->validator->isValid($uuid));
        $messages = $this->validator->getMessages();
        $this->assertCount(0, $messages);
    }

    /**
     * @param $uuid
     * @dataProvider invalidUuidProvider
     */
    public function testInvalidUuid($uuid, $expectedMessageKey)
    {
        $this->assertFalse($this->validator->isValid($uuid));
        $messages = $this->validator->getMessages();
        $this->assertCount(1, $messages);
        $this->assertArrayHasKey($expectedMessageKey, $messages);
        $this->assertNotEmpty($messages[$expectedMessageKey]);
    }

    /**
     * @return array
     */
    public function validUuidProvider()
    {
        return [
            'zero-fill' => ['00000000-0000-0000-0000-000000000000'],
            'version-1' => ['ff6f8cb0-c57d-11e1-9b21-0800200c9a66'],
            'version-2' => ['ff6f8cb0-c57d-21e1-9b21-0800200c9a66'],
            'version-3' => ['ff6f8cb0-c57d-31e1-9b21-0800200c9a66'],
            'version-4' => ['ff6f8cb0-c57d-41e1-9b21-0800200c9a66'],
            'version-5' => ['ff6f8cb0-c57d-51e1-9b21-0800200c9a66'],
            'uppercase' => ['FF6F8CB0-C57D-11E1-9B21-0800200C9A66'],
        ];
    }

    /**
     * @return array
     */
    public function invalidUuidProvider()
    {
        return [
            'invalid-characters' => ['laminas6f8cb0-c57d-11e1-9b21-0800200c9a66', Uuid::INVALID],
            'missing-separators' => ['af6f8cb0c57d11e19b210800200c9a66', Uuid::INVALID],
            'invalid-segment-2'  => ['ff6f8cb0-c57da-51e1-9b21-0800200c9a66', Uuid::INVALID],
            'invalid-segment-1'  => ['af6f8cb-c57d-11e1-9b21-0800200c9a66', Uuid::INVALID],
            'invalid-segement-5' => ['3f6f8cb0-c57d-11e1-9b21-0800200c9a6', Uuid::INVALID],
            'truncated'          => ['3f6f8cb0', Uuid::INVALID],
            'empty-string'       => ['', Uuid::INVALID],
            'all-numeric'        => [123, Uuid::NOT_STRING],
            'object'             => [new stdClass(), Uuid::NOT_STRING],
        ];
    }
}
