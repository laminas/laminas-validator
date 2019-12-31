<?php

/**
 * @see       https://github.com/laminas/laminas-validator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-validator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-validator/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Validator\TestAsset;

use Laminas\I18n\Translator\Translator as I18nTranslator;
use Laminas\Validator\Translator\TranslatorInterface as ValidatorTranslatorInterface;

class Translator extends I18nTranslator implements ValidatorTranslatorInterface
{
}
