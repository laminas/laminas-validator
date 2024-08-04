<?php

declare(strict_types=1);

namespace LaminasTest\Validator;

use DateInterval;
use Laminas\Validator\DateIntervalString;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class DateIntervalStringTest extends TestCase
{
    /** @return array<string, array{0: mixed, 1: bool, 2: string|null}> */
    public static function basicDataProvider(): array
    {
        return [
            'Int'                  => [1, false, DateIntervalString::ERR_NOT_STRING],
            'Bool'                 => [true, false, DateIntervalString::ERR_NOT_STRING],
            'Float'                => [0.5, false, DateIntervalString::ERR_NOT_STRING],
            'Date Interval Object' => [new DateInterval('P1D'), false, DateIntervalString::ERR_NOT_STRING],
            'Invalid String #1'    => ['foo', false, DateIntervalString::ERR_INVALID],
            'Invalid String #2'    => ['P1DTBAZ', false, DateIntervalString::ERR_INVALID],
            'Valid String #1'      => ['P1YT5S', true, null],
            'Valid String #2'      => ['P1Y1M1W1DT1H1M1S', true, null],
        ];
    }

    #[DataProvider('basicDataProvider')]
    public function testBasicBehaviour(mixed $value, bool $expect, string|null $errorKey): void
    {
        $validator = new DateIntervalString();
        self::assertSame($expect, $validator->isValid($value));
        if ($errorKey === null) {
            return;
        }

        self::assertArrayHasKey($errorKey, $validator->getMessages());
    }
}
