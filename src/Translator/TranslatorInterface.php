<?php

namespace Laminas\Validator\Translator;

/**
 * @deprecated Since 2.61.0 All custom translation classes will be removed in v3.0 and validators will only accept
 *             and use an instance of \Laminas\Translator\TranslatorInterface
 */
interface TranslatorInterface
{
    /**
     * @param  string $message
     * @param  string $textDomain
     * @param  string $locale
     * @return string
     */
    public function translate($message, $textDomain = 'default', $locale = null);
}
