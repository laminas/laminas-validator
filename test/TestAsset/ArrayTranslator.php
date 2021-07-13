<?php

namespace LaminasTest\Validator\TestAsset;

use Laminas\I18n\Translator as I18nTranslator;

class ArrayTranslator implements I18nTranslator\Loader\FileLoaderInterface
{
    /** @var array */
    public $translations;

    /**
     * @param string $filename
     * @param string $locale
     * @return I18n\TextDomain
     */
    public function load($filename, $locale)
    {
        return new I18nTranslator\TextDomain($this->translations);
    }
}
