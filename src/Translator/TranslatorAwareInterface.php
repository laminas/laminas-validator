<?php

declare(strict_types=1);

namespace Laminas\Validator\Translator;

use Laminas\Translator\TranslatorInterface;

interface TranslatorAwareInterface
{
    /**
     * Sets translator to use in helper
     *
     * @param  TranslatorInterface|null $translator  [optional] translator.
     *             Default is null, which sets no translator.
     * @param  string|null $textDomain  [optional] text domain
     *             Default is null, which skips setTranslatorTextDomain
     */
    public function setTranslator(?TranslatorInterface $translator = null, ?string $textDomain = null): void;

    /**
     * Returns translator used in object
     */
    public function getTranslator(): ?TranslatorInterface;

    /**
     * Checks if the object has a translator
     */
    public function hasTranslator(): bool;

    /**
     * Sets whether translator is enabled and should be used
     */
    public function setTranslatorEnabled(bool $enabled = true): void;

    /**
     * Returns whether translator is enabled and should be used
     */
    public function isTranslatorEnabled(): bool;

    /**
     * Set translation text domain
     */
    public function setTranslatorTextDomain(string $textDomain = 'default'): void;

    /**
     * Return the translation text domain
     */
    public function getTranslatorTextDomain(): string;
}
