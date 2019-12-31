<?php

/**
 * @see       https://github.com/laminas/laminas-validator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-validator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-validator/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Validator\TestAsset;

use Laminas\I18n\Translator as I18nTranslator;

class ArrayTranslator implements I18nTranslator\Loader\FileLoaderInterface
{
    public $translations;

    public function load($filename, $locale)
    {
        $textDomain = new I18nTranslator\TextDomain($this->translations);
        return $textDomain;
    }
}
