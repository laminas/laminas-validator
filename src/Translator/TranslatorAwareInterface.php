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
     *
     * @deprecated since 2.61.0 This method will be removed in 3.0
     */
    public function hasTranslator(): bool;

    /**
     * Sets whether translator is enabled and should be used
     *
     * @deprecated since 2.61.0 This method will be removed in 3.0 disable translation via the
     *            `translatorEnabled` option
     */
    public function setTranslatorEnabled(bool $enabled = true): void;

    /**
     * Returns whether translator is enabled and should be used
     *
     * @deprecated since 2.61.0 This method will be removed in 3.0
     */
    public function isTranslatorEnabled(): bool;

    /**
     * Set translation text domain
     *
     * @deprecated since 2.61.0 This method will be removed in 3.0 Use the `translatorTextDomain` option, or set
     *             the text domain at the same time as the translator via `setTranslator()`
     */
    public function setTranslatorTextDomain(string $textDomain = 'default'): void;

    /**
     * Return the translation text domain
     *
     * @deprecated since 2.61.0 This method will be removed in 3.0
     */
    public function getTranslatorTextDomain(): string;
}
