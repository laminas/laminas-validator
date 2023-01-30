<?php

declare(strict_types=1);

namespace LaminasTest\Validator;

use Laminas\ServiceManager\ServiceManager;
use Laminas\Validator\IsJsonString;
use Laminas\Validator\ValidatorPluginManager;
use PHPUnit\Framework\TestCase;

use function json_encode;

class IsJsonStringTest extends TestCase
{
    /**
     * @return array<string, array{
     *     0: int-mask-of<IsJsonString::ALLOW_*>,
     *     1: string,
     *     2: bool,
     *     3: IsJsonString::ERROR_*|null,
     * }>
     */
    public function allowProvider(): array
    {
        // phpcs:disable Generic.Files.LineLength
        return [
            'Standalone Integer'              => [IsJsonString::ALLOW_INT, '1', true, null],
            'Standalone Integer, Not Allowed' => [IsJsonString::ALLOW_ALL ^ IsJsonString::ALLOW_INT, '1', false, IsJsonString::ERROR_TYPE_NOT_ALLOWED],
            'Standalone Float'                => [IsJsonString::ALLOW_FLOAT, '1.23', true, null],
            'Standalone Float, Not Allowed'   => [IsJsonString::ALLOW_ALL ^ IsJsonString::ALLOW_FLOAT, '1.23', false, IsJsonString::ERROR_TYPE_NOT_ALLOWED],
            'Standalone True'                 => [IsJsonString::ALLOW_BOOL, 'true', true, null],
            'Standalone False'                => [IsJsonString::ALLOW_BOOL, 'false', true, null],
            'Case Sensitive True'             => [IsJsonString::ALLOW_BOOL, 'TRUE', false, IsJsonString::ERROR_INVALID_JSON],
            'Case Sensitive False'            => [IsJsonString::ALLOW_BOOL, 'FALSE', false, IsJsonString::ERROR_INVALID_JSON],
            'Standalone True, Not Allowed'    => [IsJsonString::ALLOW_ALL ^ IsJsonString::ALLOW_BOOL, 'true', false, IsJsonString::ERROR_TYPE_NOT_ALLOWED],
            'Standalone False, Not Allowed'   => [IsJsonString::ALLOW_ALL ^ IsJsonString::ALLOW_BOOL, 'false', false, IsJsonString::ERROR_TYPE_NOT_ALLOWED],
            'List Notation'                   => [IsJsonString::ALLOW_ARRAY, '["Some","List"]', true, null],
            'List Notation, Not Allowed'      => [IsJsonString::ALLOW_ALL ^ IsJsonString::ALLOW_ARRAY, '["Some","List"]', false, IsJsonString::ERROR_TYPE_NOT_ALLOWED],
            'Object Notation'                 => [IsJsonString::ALLOW_OBJECT, '{"Some":"Object"}', true, null],
            'Object Notation, Not Allowed'    => [IsJsonString::ALLOW_ALL ^ IsJsonString::ALLOW_OBJECT, '{"Some":"Object"}', false, IsJsonString::ERROR_TYPE_NOT_ALLOWED],
        ];
        // phpcs:enable Generic.Files.LineLength
    }

    /**
     * @param int-mask-of<IsJsonString::ALLOW_*> $allowed
     * @param IsJsonString::ERROR_*|null $expectedErrorKey
     * @dataProvider allowProvider
     */
    public function testBasicBehaviour(int $allowed, string $input, bool $expect, string|null $expectedErrorKey): void
    {
        $validator = new IsJsonString();
        $validator->setAllow($allowed);
        $result = $validator->isValid($input);
        self::assertSame($expect, $result);
        if ($expectedErrorKey !== null) {
            self::assertArrayHasKey($expectedErrorKey, $validator->getMessages());
        }
    }

    /** @return list<array{0: mixed}> */
    public function provideThingsThatAreNotStrings(): array
    {
        return [
            [true],
            [false],
            [
                new class () {
                },
            ],
            [[]],
            [1],
            [1.23],
            [null],
        ];
    }

    /** @dataProvider provideThingsThatAreNotStrings */
    public function testThatNonStringsAreInvalid(mixed $input): void
    {
        $validator = new IsJsonString();
        self::assertFalse($validator->isValid($input));
        self::assertArrayHasKey(IsJsonString::ERROR_NOT_STRING, $validator->getMessages());
    }

    public function testThatMaxDepthCanBeExceeded(): void
    {
        $validator = new IsJsonString();
        $input     = json_encode([
            'foo' => [
                'bar' => [
                    'baz' => 'goats',
                ],
            ],
        ]);

        $validator->setMaxDepth(1);
        self::assertFalse($validator->isValid($input));
        self::assertArrayHasKey(IsJsonString::ERROR_MAX_DEPTH_EXCEEDED, $validator->getMessages());
    }

    /** @return array<array-key, array{0: string}> */
    public function pluginManagerNameProvider(): array
    {
        return [
            [IsJsonString::class],
        ];
    }

    /** @dataProvider pluginManagerNameProvider */
    public function testThatTheValidatorCanBeRetrievedFromThePluginManagerWithDefaultConfiguration(string $name): void
    {
        $pluginManager = new ValidatorPluginManager(new ServiceManager());
        $validator     = $pluginManager->get($name);
        self::assertInstanceOf(IsJsonString::class, $validator);
    }

    /** @return array<string, array{0: string}> */
    public function invalidStringProvider(): array
    {
        return [
            'Empty String'     => [''],
            'Invalid Object'   => ['{nuts}'],
            'Invalid Array'    => ['["Foo"'],
            'Arbitrary String' => ['goats are friends'],
        ];
    }

    /** @dataProvider invalidStringProvider */
    public function testInvalidStrings(string $input): void
    {
        $validator = new IsJsonString();
        self::assertFalse($validator->isValid($input));
        self::assertArrayHasKey(IsJsonString::ERROR_INVALID_JSON, $validator->getMessages());
    }
}
