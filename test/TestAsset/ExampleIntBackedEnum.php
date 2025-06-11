<?php

declare(strict_types=1);

namespace LaminasTest\Validator\TestAsset;

enum ExampleIntBackedEnum: int
{
    case LifeMeaning = 42;
    case AllTheTwos  = 22;
    case CupOfTea    = 3;
    case PickAndMix  = 26;
}
