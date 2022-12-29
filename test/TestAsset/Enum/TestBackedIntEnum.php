<?php

declare(strict_types=1);

namespace LaminasTest\Validator\TestAsset\Enum;

enum TestBackedIntEnum: int
{
    case Foo = 1;
    case Bar = 2;
}
