<?php

/**
 * @see       https://github.com/laminas/laminas-validator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-validator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-validator/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Validator\Db\TestAsset;

use Laminas\Db\Adapter\Platform\Sql92;

class TrustingSql92Platform extends Sql92
{
    /**
     * {@inheritDoc}
     */
    public function quoteValue($value)
    {
        return $this->quoteTrustedValue($value);
    }
}
