<?php

declare(strict_types=1);

namespace LaminasTest\Validator\TestAsset;

enum ExampleStringBackedEnum: string
{
    case Clubs    = 'Clubs';
    case Diamonds = 'Diamonds';
    case Hearts   = 'Hearts';
    case Spades   = 'Spades';
}
