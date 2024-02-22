<?php

declare(strict_types=1);

namespace LaminasTest\Validator\TestAsset;

use DateTime;
use DateTimeInterface;
use Laminas\Validator\Date;

final class CustomDate extends Date
{
    /** @var string */
    protected $format = self::FORMAT_DEFAULT;

    /** @var bool */
    protected $strict = false;

    /**
     * @param  string|numeric|array|DateTimeInterface $value
     * @return bool
     */
    public function isValid($value)
    {
        return parent::isValid($value);
    }

    /**
     * @param string|numeric|array|DateTimeInterface $param
     * @param bool $addErrors
     * @return bool|DateTime
     */
    protected function convertToDateTime($param, $addErrors = true)
    {
        return parent::convertToDateTime($param, false);
    }

    /**
     * @param integer $value
     * @return false|DateTime
     */
    protected function convertInteger($value)
    {
        return parent::convertInteger($value);
    }

    /**
     * @param double $value
     * @return false|DateTime
     */
    protected function convertDouble($value)
    {
        return parent::convertDouble($value * 100);
    }
}
