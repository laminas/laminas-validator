<?php

declare(strict_types=1);

use Boundwize\StructArmed\Architecture;
use Boundwize\StructArmed\Preset\Preset;

return Architecture::define()
    ->withPresets(Preset::PER(), Preset::CODEQUALITY())
    ->layer('Exception', 'src/Exception')
    ->layer('Translator', 'src/Translator')
    ->layer('Base', [
        'src/AbstractValidator.php',
        'src/ValidatorInterface.php',
        'src/ValidatorChainInterface.php',
    ])
    ->layer('Barcode', 'src/Barcode')
    ->layer('Hostname', 'src/Hostname')
    ->layer('File', 'src/File')
    ->layer('Sitemap', 'src/Sitemap')
    ->layer('Service', [
        'src/Conditional.php',
        'src/ConditionalFactory.php',
        'src/Explode.php',
        'src/ValidatorChain.php',
        'src/ValidatorChainFactory.php',
        'src/ValidatorChainFactoryFactory.php',
        'src/ValidatorChainInvokableFactory.php',
        'src/ValidatorPluginManager.php',
        'src/ValidatorPluginManagerAwareInterface.php',
        'src/ValidatorPluginManagerFactory.php',
    ])
    ->layer('Config', [
        'src/ConfigProvider.php',
        'src/Module.php',
    ])
    ->layer('Validator', 'src', [
        'src/Exception',
        'src/Translator',
        'src/AbstractValidator.php',
        'src/ValidatorInterface.php',
        'src/ValidatorChainInterface.php',
        'src/Barcode',
        'src/Hostname',
        'src/File',
        'src/Sitemap',
        'src/Conditional.php',
        'src/ConditionalFactory.php',
        'src/Explode.php',
        'src/ValidatorChain.php',
        'src/ValidatorChainFactory.php',
        'src/ValidatorChainFactoryFactory.php',
        'src/ValidatorChainInvokableFactory.php',
        'src/ValidatorPluginManager.php',
        'src/ValidatorPluginManagerAwareInterface.php',
        'src/ValidatorPluginManagerFactory.php',
        'src/ConfigProvider.php',
        'src/Module.php',
    ])
    ->ruleset([
        'Exception'  => [],
        'Translator' => [],
        'Base'       => ['Exception', 'Translator'],
        'Barcode'    => [],
        'Hostname'   => [],
        'Validator'  => ['+Base', 'Barcode', 'Hostname'],
        'File'       => ['+Base'],
        'Sitemap'    => ['+Validator'],
        'Service'    => ['+Sitemap', '+File'],
        'Config'     => ['+Service'],
    ]);
