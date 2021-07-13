<?php

namespace LaminasTest\Validator;

use Laminas\Validator\Exception\InvalidArgumentException;
use Laminas\Validator\Timezone;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * Tests for {@see \Laminas\Validator\Timezone}
 *
 * @covers \Laminas\Validator\Timezone
 */
class TimezoneTest extends TestCase
{
    /** @var Timezone */
    protected $validator;

    protected function setUp(): void
    {
        $this->validator = new Timezone();
    }

    /**
     * Test locations
     *
     * @dataProvider locationProvider
     */
    public function testLocations(?string $value, bool $valid): void
    {
        $this->validator->setType(Timezone::LOCATION);
        $this->checkValidationValue($value, $valid);
    }

    /**
     * Test locations by type is string
     *
     * @dataProvider locationProvider
     */
    public function testLocationsByTypeAsString(?string $value, bool $valid): void
    {
        $this->validator->setType('location');
        $this->checkValidationValue($value, $valid);
    }

    /**
     * Provides location values
     *
     * @psalm-return array<array-key, array{0: string|null, 1: bool}>
     */
    public function locationProvider(): array
    {
        return [
            ['America/Anguilla', true],
            ['Antarctica/Palmer', true],
            ['Asia/Dubai', true],
            ['Atlantic/Cape_Verde', true],
            ['Australia/Broken_Hill', true],
            ['America/Sao_Paulo', true],
            ['America/Toronto', true],
            ['Pacific/Easter', true],
            ['Europe/Copenhagen', true],
            ['Indian/Maldives', true],
            ['cest', false], // abbreviation of Anadyr Summer Time
            ['Asia/London', false], // wrong location
            ['', false], // empty string
            [null, false], // null value
        ];
    }

    /**
     * Test abbreviations
     *
     * @dataProvider abbreviationProvider
     */
    public function testAbbreviations(?string $value, bool $valid): void
    {
        $this->validator->setType(Timezone::ABBREVIATION);
        $this->checkValidationValue($value, $valid);
    }

    /**
     * Test abbreviations byTypeAsString
     *
     * @dataProvider abbreviationProvider
     */
    public function testAbbreviationsByTypeAsString(?string $value, bool $valid): void
    {
        $this->validator->setType('abbreviation');
        $this->checkValidationValue($value, $valid);
    }

    /**
     * Provides abbreviation values
     *
     * @return array<array-key, array{0: null|string, 1: bool}>
     */
    public function abbreviationProvider(): array
    {
        return [
            ['cest', true], // Central European Summer Time
            ['hkt', true], // Hong Kong Time
            ['nzdt', true], // New Zealand Daylight Time
            ['sast', true], // South Africa Standard Time
            ['America/Toronto', false], // location
            ['xyz', false], // wrong abbreviation
            ['', false], // empty string
            [null, false], // null value
        ];
    }

    /**
     * Test locations and abbreviations
     *
     * @dataProvider locationAndAbbreviationProvider
     */
    public function testlocationsAndAbbreviationsWithAllTypeAsString(?string $value, bool $valid): void
    {
        $this->validator->setType(Timezone::ALL);
        $this->checkValidationValue($value, $valid);
    }

    /**
     * Test locations and abbreviations
     *
     * @dataProvider locationAndAbbreviationProvider
     */
    public function testlocationsAndAbbreviationsWithAllTypeAsArray(?string $value, bool $valid): void
    {
        $this->validator->setType([Timezone::LOCATION, Timezone::ABBREVIATION]);
        $this->checkValidationValue($value, $valid);
    }

    /**
     * Test locations and abbreviations
     *
     * @dataProvider locationAndAbbreviationProvider
     */
    public function testLocationsAndAbbreviationsWithAllTypeAsArrayWithStrings(?string $value, bool $valid): void
    {
        $this->validator->setType(['location', 'abbreviation']);
        $this->checkValidationValue($value, $valid);
    }

    /**
     * Provides location and abbreviation values
     *
     * @psalm-return array<array-key, array{0: string|null, 1: bool}>
     */
    public function locationAndAbbreviationProvider(): array
    {
        return [
            ['America/Anguilla', true],
            ['Antarctica/Palmer', true],
            ['Asia/Dubai', true],
            ['Atlantic/Cape_Verde', true],
            ['Australia/Broken_Hill', true],
            ['hkt', true], // Hong Kong Time
            ['nzdt', true], // New Zealand Daylight Time
            ['sast', true], // South Africa Standard Time
            ['xyz', false], // wrong abbreviation
            ['Asia/London', false], // wrong location
            ['', false], // empty string
            [null, false], // null value
        ];
    }

    /**
     * Test wrong type
     *
     * @dataProvider wrongTypesProvider
     * @param mixed $value
     */
    public function testWrongType($value): void
    {
        $this->checkExpectedException($value);
    }

    /**
     * Provides wrong types
     *
     * @psalm-return array<array-key, array{0: mixed}>
     */
    public function wrongTypesProvider(): array
    {
        return [
            [null],
            [''],
            [[]],
            [0],
            [4],
        ];
    }

    /**
     * Test pass `type` option through constructor
     *
     *  @return void
     */
    public function testTypeThroughConstructor()
    {
        $timezone1 = new Timezone(Timezone::LOCATION);
        $this->assertTrue($timezone1->isValid('Asia/Dubai'));
        $this->assertFalse($timezone1->isValid('sast'));

        $timezone2 = new Timezone('location');
        $this->assertTrue($timezone2->isValid('Asia/Dubai'));
        $this->assertFalse($timezone2->isValid('sast'));

        $timezone3 = new Timezone(['type' => 'location']);
        $this->assertTrue($timezone3->isValid('Asia/Dubai'));
        $this->assertFalse($timezone3->isValid('sast'));

        $timezone4 = new Timezone(Timezone::ABBREVIATION);
        $this->assertFalse($timezone4->isValid('Asia/Dubai'));
        $this->assertTrue($timezone4->isValid('sast'));

        $timezone5 = new Timezone('abbreviation');
        $this->assertFalse($timezone5->isValid('Asia/Dubai'));
        $this->assertTrue($timezone5->isValid('sast'));

        $timezone6 = new Timezone(['type' => 'abbreviation']);
        $this->assertFalse($timezone6->isValid('Asia/Dubai'));
        $this->assertTrue($timezone6->isValid('sast'));

        // default value is `all`
        $timezone7 = new Timezone();
        $this->assertTrue($timezone7->isValid('Asia/Dubai'));
        $this->assertTrue($timezone7->isValid('sast'));

        $timezone8 = new Timezone(['type' => ['location', 'abbreviation']]);
        $this->assertTrue($timezone8->isValid('Asia/Dubai'));
        $this->assertTrue($timezone8->isValid('sast'));
    }

    /**
     * @param mixed $invalidType
     * @dataProvider getInvalidTypes
     */
    public function testRejectsInvalidIntType($invalidType): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Timezone(['type' => $invalidType]);
    }

    /**
     * Checks that the validation value matches the expected validity
     *
     * @param mixed $value Value to validate
     * @param bool  $valid Expected validity
     * @return void
     */
    protected function checkValidationValue($value, $valid)
    {
        $isValid = $this->validator->isValid($value);

        if ($valid) {
            $this->assertTrue($isValid);
        } else {
            $this->assertFalse($isValid);
        }
    }

    /**
     * Checks expected exception on wrong type
     *
     * @param mixed $value Value to validate
     * @return void
     */
    protected function checkExpectedException($value)
    {
        $this->expectException(InvalidArgumentException::class);
        $this->validator->setType($value);
    }

    /**
     * Data provider
     *
     * @return mixed[][]
     */
    public function getInvalidTypes()
    {
        return [
            [new stdClass()],
            [[]],
            [0],
            [10],
            ['foo'],
        ];
    }
}
