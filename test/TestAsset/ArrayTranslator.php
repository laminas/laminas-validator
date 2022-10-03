<?php

declare(strict_types=1);

namespace LaminasTest\Validator\TestAsset;

use Laminas\I18n\Translator as I18nTranslator;

final class ArrayTranslator implements I18nTranslator\Loader\FileLoaderInterface
{
    public array $translations;

    /**
     * @param string $filename
     * @param string $locale
     */
    public function load($filename, $locale): I18nTranslator\TextDomain
    {
        return new I18nTranslator\TextDomain($this->translations);
    }
}
