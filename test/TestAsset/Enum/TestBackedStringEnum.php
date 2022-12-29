<?php

declare(strict_types=1);

namespace LaminasTest\Validator\TestAsset\Enum;

enum TestBackedStringEnum: string
{
    case Foo = 'foo';
    case Bar = 'bar';
}
