<?php

declare(strict_types=1);

namespace LaminasTest\Validator\TestAsset;

use Laminas\Validator\CreditCard;

final class CreditCardValidatorExtension extends CreditCard
{
    public const TEST_TYPE = 'Test_Type';
}
