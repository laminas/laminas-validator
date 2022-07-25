<?php

declare(strict_types=1);

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
