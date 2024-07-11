<?php

namespace Laminas\Validator\Translator;

interface TranslatorAwareInterface
{
    /**
     * Sets translator to use in helper
     *
     * @param  TranslatorInterface $translator  [optional] translator.
     *             Default is null, which sets no translator.
     * @param  string $textDomain  [optional] text domain
     *             Default is null, which skips setTranslatorTextDomain
     * @return self
     */
    public function setTranslator(?TranslatorInterface $translator = null, $textDomain = null);

    /**
     * Returns translator used in object
     *
     * @return TranslatorInterface|null
     */
    public function getTranslator();

    /**
     * Checks if the object has a translator
     *
     * @deprecated since 2.61.0 This method will be removed in 3.0
     *
     * @return bool
     */
    public function hasTranslator();

    /**
     * Sets whether translator is enabled and should be used
     *
     * @deprecated since 2.61.0 This method will be removed in 3.0 disable translation via the
     *            `translatorEnabled` option
     *
     * @param bool $enabled [optional] whether translator should be used.
     *                      Default is true.
     * @return self
     */
    public function setTranslatorEnabled($enabled = true);

    /**
     * Returns whether translator is enabled and should be used
     *
     * @deprecated since 2.61.0 This method will be removed in 3.0
     *
     * @return bool
     */
    public function isTranslatorEnabled();

    /**
     * Set translation text domain
     *
     * @deprecated since 2.61.0 This method will be removed in 3.0 Use the `translatorTextDomain` option, or set
     *             the text domain at the same time as the translator via `setTranslator()`
     *
     * @param string $textDomain
     * @return TranslatorAwareInterface
     */
    public function setTranslatorTextDomain($textDomain = 'default');

    /**
     * Return the translation text domain
     *
     * @deprecated since 2.61.0 This method will be removed in 3.0
     *
     * @return string
     */
    public function getTranslatorTextDomain();
}
