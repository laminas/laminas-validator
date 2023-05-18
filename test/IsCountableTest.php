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

final class IsCountableTest extends TestCase
{
    /**
     * @psalm-return array<string, array{0: array<string, mixed>}>
     */
    public static function conflictingOptionsProvider(): array
    {
        return [
            'count-min' => [['count' => 10, 'min' => 1]],
            'count-max' => [['count' => 10, 'max' => 10]],
        ];
    }

    #[DataProvider('conflictingOptionsProvider')]
    public function testConstructorRaisesExceptionWhenProvidedConflictingOptions(array $options): void
    {
        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('conflicts');

        new IsCountable($options);
    }

    /**
     * @psalm-return array<string, array{0: array<string, mixed>, 1: array<string, mixed>}>
     */
    public static function conflictingSecondaryOptionsProvider(): array
    {
        return [
            'count-min' => [['count' => 10], ['min' => 1]],
            'count-max' => [['count' => 10], ['max' => 10]],
        ];
    }

    #[DataProvider('conflictingSecondaryOptionsProvider')]
    public function testSetOptionsRaisesExceptionWhenProvidedOptionConflictingWithCurrentSettings(
        array $originalOptions,
        array $secondaryOptions
    ): void {
        $validator = new IsCountable($originalOptions);

        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('conflicts');

        $validator->setOptions($secondaryOptions);
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
        self::assertCount(1, $sut->getMessages());
    }

    public function testInvalidType(): void
    {
        $sut = new IsCountable();

        self::assertFalse($sut->isValid(new stdClass()));
        self::assertCount(1, $sut->getMessages());
    }

    public function testInvalidExceedsMax(): void
    {
        $sut = new IsCountable([
            'max' => 1,
        ]);

        self::assertFalse($sut->isValid(['Foo', 'Bar']));
        self::assertCount(1, $sut->getMessages());
    }

    public function testInvalidExceedsMin(): void
    {
        $sut = new IsCountable([
            'min' => 2,
        ]);

        self::assertFalse($sut->isValid(['Foo']));
        self::assertCount(1, $sut->getMessages());
    }
}
