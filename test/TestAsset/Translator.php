<?php

declare(strict_types=1);

namespace LaminasTest\Validator\TestAsset;

use Laminas\Translator\TranslatorInterface;

class Translator implements TranslatorInterface
{
    /** @param array<string, string> $translations */
    public function __construct(public array $translations)
    {
    }

    /** @inheritDoc */
    public function translate($message, $textDomain = 'default', $locale = null)
    {
        return $this->translations[$message] ?? $message;
    }

    /** @inheritDoc */
    public function translatePlural($singular, $plural, $number, $textDomain = 'default', $locale = null)
    {
        return $number === 1 ? $singular : $plural;
    }
}
