<?php

/**
 * @see       https://github.com/laminas/laminas-validator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-validator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-validator/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Validator;

use Laminas\Validator\Exception;
use Laminas\Validator\IsCountable;
use PHPUnit\Framework\TestCase;

class IsCountableTest extends TestCase
{
    /**
     * @psalm-return array<string, array{0: array<string, mixed>}>
     */
    public function conflictingOptionsProvider(): array
    {
        return [
            'count-min' => [['count' => 10, 'min' => 1]],
            'count-max' => [['count' => 10, 'max' => 10]],
        ];
    }

    /**
     * @dataProvider conflictingOptionsProvider
     */
    public function testConstructorRaisesExceptionWhenProvidedConflictingOptions(array $options): void
    {
        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('conflicts');
        new IsCountable($options);
    }

    /**
     * @psalm-return array<string, array{0: array<string, mixed>, 1: array<string, mixed>}>
     */
    public function conflictingSecondaryOptionsProvider(): array
    {
        return [
            'count-min' => [['count' => 10], ['min' => 1]],
            'count-max' => [['count' => 10], ['max' => 10]],
        ];
    }

    /**
     * @dataProvider conflictingSecondaryOptionsProvider
     */
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

        $this->assertTrue($sut->isValid(['Foo']), json_encode($sut->getMessages()));
        $this->assertCount(0, $sut->getMessages());
    }

    public function testIteratorIsValid(): void
    {
        $sut = new IsCountable();

        $this->assertTrue($sut->isValid(new \SplQueue()), json_encode($sut->getMessages()));
        $this->assertCount(0, $sut->getMessages());
    }

    public function testValidEquals(): void
    {
        $sut = new IsCountable([
            'count' => 1,
        ]);

        $this->assertTrue($sut->isValid(['Foo']));
        $this->assertCount(0, $sut->getMessages());
    }

    public function testValidMax(): void
    {
        $sut = new IsCountable([
            'max' => 1,
        ]);

        $this->assertTrue($sut->isValid(['Foo']));
        $this->assertCount(0, $sut->getMessages());
    }

    public function testValidMin(): void
    {
        $sut = new IsCountable([
            'min' => 1,
        ]);

        $this->assertTrue($sut->isValid(['Foo']));
        $this->assertCount(0, $sut->getMessages());
    }

    public function testInvalidNotEquals(): void
    {
        $sut = new IsCountable([
            'count' => 2,
        ]);

        $this->assertFalse($sut->isValid(['Foo']));
        $this->assertCount(1, $sut->getMessages());
    }

    public function testInvalidType(): void
    {
        $sut = new IsCountable();

        $this->assertFalse($sut->isValid(new \stdClass()));
        $this->assertCount(1, $sut->getMessages());
    }

    public function testInvalidExceedsMax(): void
    {
        $sut = new IsCountable([
            'max' => 1,
        ]);

        $this->assertFalse($sut->isValid(['Foo', 'Bar']));
        $this->assertCount(1, $sut->getMessages());
    }

    public function testInvalidExceedsMin(): void
    {
        $sut = new IsCountable([
            'min' => 2,
        ]);

        $this->assertFalse($sut->isValid(['Foo']));
        $this->assertCount(1, $sut->getMessages());
    }
}
