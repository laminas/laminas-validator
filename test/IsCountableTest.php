<?php

declare(strict_types=1);

namespace LaminasTest\Validator;

use Laminas\Validator\Exception;
use Laminas\Validator\IsCountable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use SplQueue;
use stdClass;

use function json_encode;

use const JSON_THROW_ON_ERROR;

/** @psalm-import-type OptionsArgument from IsCountable */
final class IsCountableTest extends TestCase
{
    /**
     * @psalm-return array<string, array{0: array<string, mixed>}>
     */
    public static function conflictingOptionsProvider(): array
    {
        return [
            'count-min'     => [['count' => 10, 'min' => 1]],
            'count-max'     => [['count' => 10, 'max' => 10]],
            'invalid range' => [['min' => 20, 'max' => 10]],
        ];
    }

    /** @param OptionsArgument $options */
    #[DataProvider('conflictingOptionsProvider')]
    public function testConstructorRaisesExceptionWhenProvidedConflictingOptions(array $options): void
    {
        $this->expectException(Exception\InvalidArgumentException::class);

        new IsCountable($options);
    }

    public function testArrayIsValid(): void
    {
        $sut = new IsCountable([
            'min' => 1,
            'max' => 10,
        ]);

        self::assertTrue($sut->isValid(['Foo']), json_encode($sut->getMessages(), JSON_THROW_ON_ERROR));
        self::assertCount(0, $sut->getMessages());
    }

    public function testIteratorIsValid(): void
    {
        $sut = new IsCountable();

        self::assertTrue($sut->isValid(new SplQueue()), json_encode($sut->getMessages(), JSON_THROW_ON_ERROR));
        self::assertCount(0, $sut->getMessages());
    }

    public function testValidEquals(): void
    {
        $sut = new IsCountable([
            'count' => 1,
        ]);

        self::assertTrue($sut->isValid(['Foo']));
        self::assertCount(0, $sut->getMessages());
    }

    public function testValidMax(): void
    {
        $sut = new IsCountable([
            'max' => 1,
        ]);

        self::assertTrue($sut->isValid(['Foo']));
        self::assertCount(0, $sut->getMessages());
    }

    public function testValidMin(): void
    {
        $sut = new IsCountable([
            'min' => 1,
        ]);

        self::assertTrue($sut->isValid(['Foo']));
        self::assertCount(0, $sut->getMessages());
    }

    public function testInvalidNotEquals(): void
    {
        $sut = new IsCountable([
            'count' => 2,
        ]);

        self::assertFalse($sut->isValid(['Foo']));
        self::assertArrayHasKey(IsCountable::NOT_EQUALS, $sut->getMessages());
    }

    public function testInvalidType(): void
    {
        $sut = new IsCountable(['count' => 99]);

        self::assertFalse($sut->isValid(new stdClass()));
        self::assertArrayHasKey(IsCountable::NOT_COUNTABLE, $sut->getMessages());
    }

    public function testInvalidExceedsMax(): void
    {
        $sut = new IsCountable([
            'max' => 1,
        ]);

        self::assertFalse($sut->isValid(['Foo', 'Bar']));
        self::assertArrayHasKey(IsCountable::GREATER_THAN, $sut->getMessages());
    }

    public function testInvalidExceedsMin(): void
    {
        $sut = new IsCountable([
            'min' => 2,
        ]);

        self::assertFalse($sut->isValid(['Foo']));
        self::assertArrayHasKey(IsCountable::LESS_THAN, $sut->getMessages());
    }
}
