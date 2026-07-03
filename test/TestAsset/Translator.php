<?php

declare(strict_types=1);

namespace LaminasTest\Validator\TestAsset;

use Laminas\Translator\TranslatorInterface;

class Translator implements TranslatorInterface
{
    /** @param array<string, string> $translations */
    public function __construct(
        public array $translations,
    ) {}

    /** @inheritDoc */
    public function translate(
        string $message,
        string $textDomain = self::DEFAULT_TEXT_DOMAIN,
        ?string $locale = null,
    ): string {
        return $this->translations[$message] ?? $message;
    }

    /** @inheritDoc */
    public function translatePlural(
        string $singular,
        string $plural,
        int $number,
        string $textDomain = self::DEFAULT_TEXT_DOMAIN,
        ?string $locale = null,
    ): string {
        return $number === 1 ? $singular : $plural;
    }
}
