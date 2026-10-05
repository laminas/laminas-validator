<?php

declare(strict_types=1);

use Boundwize\StructArmed\Architecture;
use Boundwize\StructArmed\Preset\Preset;

return Architecture::define()
    ->withPresets(Preset::PER(), Preset::CODEQUALITY())
    ->layer('Exception', 'src/Exception')
    ->layer('Translator', 'src/Translator')
    ->layer('BaseValidator', [
        'src/AbstractValidator.php',
        'src/ValidatorInterface.php',
        'src/ValidatorChainInterface.php',
    ])
    ->layer('Barcode', 'src/Barcode')
    ->layer('Hostname', 'src/Hostname')
    ->layer('FileValidator', 'src/File')
    ->layer('SitemapValidator', 'src/Sitemap')
    ->layer('Config', [
        'src/ConfigProvider.php',
        'src/Module.php',
    ])
    ->layer('CommonValidator', 'src', [
        'src/Exception',
        'src/Translator',
        'src/AbstractValidator.php',
        'src/ValidatorInterface.php',
        'src/ValidatorChainInterface.php',
        'src/Barcode',
        'src/Hostname',
        'src/File',
        'src/Sitemap',
        'src/ConfigProvider.php',
        'src/Module.php',
    ])
    ->ruleset([
        'Exception'        => [],
        'Translator'       => [],
        'BaseValidator'    => ['Exception', 'Translator'],
        'Barcode'          => [],
        'Hostname'         => [],
        'CommonValidator'  => ['+FileValidator', 'Barcode', 'Hostname', 'SitemapValidator'],
        'FileValidator'    => ['+BaseValidator'],
        'SitemapValidator' => ['+CommonValidator'],
        'Config'           => ['+CommonValidator'],
    ]);
