<?php

declare(strict_types=1);

namespace LaminasTest\Validator\TestAsset;

use Laminas\I18n\Translator\Translator as I18nTranslator;
use Laminas\Validator\Translator\TranslatorInterface as ValidatorTranslatorInterface;

class Translator extends I18nTranslator implements ValidatorTranslatorInterface
{
}
