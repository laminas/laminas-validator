<?php

/**
 * @see       https://github.com/laminas/laminas-validator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-validator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-validator/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Validator;

use Laminas\Validator\GpsPoint;
use PHPUnit\Framework\TestCase;

/**
 * @group      Laminas_Validator
 */
class GPSPointTest extends TestCase
{

    /**
     * @var GpsPoint
     */
    protected $validator;

    protected function setUp() : void
    {
        $this->validator = new GpsPoint();
    }

    /**
     * @dataProvider basicDataProvider
     *
     * @covers \Laminas\Validator\GPSPoint::isValid
     *
     * @return void
     */
    public function testBasic($gpsPoint): void
    {
        $this->assertTrue($this->validator->isValid($gpsPoint));
    }

    /**
     * @covers \Laminas\Validator\GPSPoint::isValid
     *
     * @return void
     */
    public function testBoundariesAreRespected(): void
    {
        $this->assertFalse($this->validator->isValid('181.8897,-77.0089'));
        $this->assertFalse($this->validator->isValid('38.8897,-181.0089'));
        $this->assertFalse($this->validator->isValid('-181.8897,-77.0089'));
        $this->assertFalse($this->validator->isValid('38.8897,181.0089'));
    }

    /**
     * @covers \Laminas\Validator\GPSPoint::isValid
     *
     * @dataProvider errorMessageTestValues
     *
     * @return void
     */
    public function testErrorsSetOnOccur($value, $messageKey, $messageValue): void
    {
        $this->assertFalse($this->validator->isValid($value));
        $messages = $this->validator->getMessages();
        $this->assertArrayHasKey($messageKey, $messages);
        $this->assertStringContainsString($messageValue, $messages[$messageKey]);
    }

    /**
     * @psalm-return array<array-key, array{0: string}>
     */
    public function basicDataProvider(): array
    {
        return [
            ['38° 53\' 23" N, 77° 00\' 32" W'],
            ['15° 22\' 20.137" S, 35° 35\' 14.686" E'],
            ['65° 4\' 36.434" N,-22.728867530822754'],
            ['38.8897°, -77.0089°'],
            ['38.8897,-77.0089'],
        ];
    }

    /**
     * @psalm-return array<array-key, array{0: string, 1: string, 2: string}>
     */
    public function errorMessageTestValues(): array
    {
        return [
            ['63 47 24.691 N, 18 2 54.363 W', GpsPoint::OUT_OF_BOUNDS, '63 47 24.691 N'],
            ['° \' " N,° \' " E', GpsPoint::CONVERT_ERROR, '° \' " N'],
            ['° \' " N', GpsPoint::INCOMPLETE_COORDINATE, '° \' " N'],
        ];
    }
}
