<?php

/**
 * @see       https://github.com/laminas/laminas-validator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-validator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-validator/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Validator\TestAsset;

use Laminas\Validator\EmailAddress;

/**
 * @see Laminas-12347
 */
class EmailValidatorWithExposedIsReserved extends EmailAddress
{
    public function isReserved($host)
    {
        return parent::isReserved($host);
    }
}
