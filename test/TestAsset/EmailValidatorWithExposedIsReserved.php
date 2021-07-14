<?php

namespace LaminasTest\Validator\TestAsset;

use Laminas\Validator\EmailAddress;

/**
 * @see Laminas-12347
 */
class EmailValidatorWithExposedIsReserved extends EmailAddress
{
    /**
     * @param string $host
     * @return bool
     */
    public function isReserved($host)
    {
        return parent::isReserved($host);
    }
}
