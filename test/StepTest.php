<?php

namespace LaminasTest\Validator;

use Laminas\Validator;
use Laminas\Validator\Step;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class StepTest extends TestCase
{
    /**
     * Step object
     *
     * @var Step
     */
    protected $validator;

    /**
     * Creates a new Laminas\Validator\Step object for each test method
     */
    protected function setUp(): void
    {
        $this->validator = new Validator\Step();
    }

    /**
     * @psalm-return array<string, array{0: float|int|string, 1: bool}>
     */
    public function valuesToValidate(): array
    {
        return [
            'float'              => [1.00, true],
            'zero-float'         => [0.00, true],
            'int-2'              => [2, true],
            'int-3'              => [3, true],
            'float-fraction'     => [2.1, false],
            'string-2'           => ['2', true],
            'string-1'           => ['1', true],
            'string-decimal'     => ['1.2', false],
            'string-hundredths'  => [1.01, false],
            'string-non-decimal' => ['not a scalar', false],
        ];
    }

    /**
     * Ensures that the validator follows expected behavior
     *
     * @dataProvider valuesToValidate
     * @param mixed $value
     */
    public function testBasic($value, bool $expected): void
    {
        // By default, baseValue == 0 and step == 1
        $this->assertSame(
            $expected,
            $this->validator->isValid($value)
        );
    }

    /**
     * @psalm-return array<string, array{0: float|string, 1: bool}>
     */
    public function decimalValues(): array
    {
        return [
            'between-step'        => [1.1, false],
            'base-value'          => [0.1, true],
            'first-step'          => [2.1, true],
            'between-steps'       => [3.1, false],
            'string-first-step'   => ['2.1', true],
            'string-between-step' => ['1.1', false],
            'fine-grained'        => [1.11, false],
            'string-non-decimal'  => ['not a scalar', false],
        ];
    }

    /**
     * @dataProvider decimalValues
     * @param mixed $value
     */
    public function testDecimalBaseValue($value, bool $expected): void
    {
        $validator = new Validator\Step([
            'baseValue' => 0.1,
            'step'      => 2,
        ]);

        $this->assertSame($expected, $validator->isValid($value));
    }

    /**
     * @psalm-return array<string, array{0: float|string, 1: bool}>
     */
    public function decimalStepValues(): array
    {
        return [
            'between-0.1'        => [0.1, false],
            'between-1.1'        => [1.1, false],
            'first-step'         => [2.1, true],
            'between-3.1'        => [3.1, false],
            'second-step'        => [4.2, true],
            'third-step'         => [6.3, true],
            'fourth-step'        => [8.4, true],
            'fifth-step'         => [10.5, true],
            'sixth-step'         => [12.6, true],
            'seventh-step'       => [14.7, true],
            'eight-step'         => [16.8, true],
            'ninth-step'         => [18.9, true],
            'tenth-step'         => [21.0, true],
            'string-1.1'         => ['1.1', false],
            'string-1.11'        => [1.11, false],
            'string-first-step'  => ['2.1', true],
            'string-non-decimal' => ['not a scalar', false],
        ];
    }

    /**
     * @dataProvider decimalStepValues
     * @param mixed $value
     */
    public function testDecimalStep($value, bool $expected): void
    {
        $validator = new Validator\Step([
            'baseValue' => 0,
            'step'      => 2.1,
        ]);
        $this->assertSame($expected, $validator->isValid($value));
    }

    /**
     * @psalm-return array<string, array{0: int, 1: float, 2: bool}>
     */
    public function decimalStepSubstractionBugValues(): array
    {
        return [
            'base-value-20' => [20, 20.06, true],
            'base-value-40' => [40, 40.09, true],
            'base-value-50' => [50, 50.09, true],
        ];
    }

    /**
     * @dataProvider decimalStepSubstractionBugValues
     */
    public function testDecimalStepSubstractionBug(int $baseValue, float $value, bool $expected): void
    {
        $validator = new Validator\Step([
            'baseValue' => $baseValue,
            'step'      => 0.01,
        ]);

        $this->assertSame($expected, $validator->isValid($value));
    }

    /**
     * @psalm-return array<string, array{0: float, 1: bool}>
     */
    public function decimalHundredthStepValues(): array
    {
        return [
            'first-step'       => [0.01, true],
            'second-step'      => [0.02, true],
            'third-step'       => [0.03, true],
            'fourth-step'      => [0.04, true],
            'fifth-step'       => [0.05, true],
            'sixth-step'       => [0.06, true],
            'seventh-step'     => [0.07, true],
            'eighth-step'      => [0.08, true],
            'ninth-step'       => [0.09, true],
            'thousandth-0.001' => [0.001, false],
            'thousandth-0.002' => [0.002, false],
            'thousandth-0.003' => [0.003, false],
            'thousandth-0.004' => [0.004, false],
            'thousandth-0.005' => [0.005, false],
            'thousandth-0.006' => [0.006, false],
            'thousandth-0.007' => [0.007, false],
            'thousandth-0.008' => [0.008, false],
            'thousandth-0.009' => [0.009, false],
        ];
    }

    /**
     * @dataProvider decimalHundredthStepValues
     */
    public function testdecimalHundredthStep(float $value, bool $expected): void
    {
        $validator = new Validator\Step([
            'baseValue' => 0,
            'step'      => 0.01,
        ]);

        $this->assertSame($expected, $validator->isValid($value));
    }

    /**
     * Ensures that getMessages() returns expected default value
     *
     * @return void
     */
    public function testGetMessages()
    {
        $this->assertEquals([], $this->validator->getMessages());
    }

    /**
     * Ensures that set/getBaseValue() works
     */
    public function testCanSetBaseValue(): void
    {
        $this->validator->setBaseValue(2);
        $this->assertEquals('2', $this->validator->getBaseValue());
    }

    /**
     * Ensures that set/getStep() works
     */
    public function testCanSetStepValue(): void
    {
        $this->validator->setStep(2);
        $this->assertEquals('2', $this->validator->getStep());
    }

    public function testEqualsMessageTemplates(): void
    {
        $validator = new Validator\Step();
        $this->assertObjectHasAttribute('messageTemplates', $validator);
        $this->assertEquals($validator->getOption('messageTemplates'), $validator->getMessageTemplates());
    }

    public function testSetStepFloat(): void
    {
        $step = 0.01;
        $this->validator->setStep($step);
        $this->assertObjectHasAttribute('step', $this->validator);
        $this->assertEquals($step, $this->validator->getStep());
    }

    public function testSetStepString(): void
    {
        $step = '0.01';
        $this->validator->setStep($step);
        $this->assertObjectHasAttribute('step', $this->validator);
        $this->assertEquals((float) $step, $this->validator->getStep());
    }

    public function testConstructorCanAcceptAllOptionsAsDiscreteArguments(): void
    {
        $baseValue = 1.00;
        $step      = 0.01;
        $validator = new Validator\Step($baseValue, $step);

        $this->assertSame($step, $validator->getStep());
        $this->assertSame($baseValue, $validator->getBaseValue());
    }

    public function testFModNormalizesZeroToFloatOne(): void
    {
        $validator = new Validator\Step();

        $r = new ReflectionMethod($validator, 'fmod');
        $r->setAccessible(true);

        $this->assertSame(1.0, $r->invoke($validator, 0, 0));
    }
}
