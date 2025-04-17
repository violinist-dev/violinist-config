<?php

namespace Violinist\Config\Tests\VersionThree;

use PHPUnit\Framework\TestCase;
use Violinist\Config\Config;

class MergedConfigValidationTest extends TestCase
{
    public function testFinalMergedOutputIsCorrect()
    {
        $temp_folder = sys_get_temp_dir() . '/' . uniqid('ViolinistMergedTest_', true);
        mkdir($temp_folder, 0777, true);

        // Root config that extends shared-violinist-drupal
        $composer_data = (object) [
            'extra' => (object) [
                'violinist' => (object) [
                    'extends' => 'vendor/shared-violinist-drupal',
                ],
            ],
        ];
        file_put_contents("$temp_folder/composer.json", json_encode($composer_data));

        // Mock vendor structure
        mkdir("$temp_folder/vendor/vendor/shared-violinist-drupal", 0777, true);
        mkdir("$temp_folder/vendor/vendor/shared-violinist-common", 0777, true);

        // Copy fixture configs
        copy(__DIR__ . '/../fixtures/level1.json', "$temp_folder/vendor/vendor/shared-violinist-drupal/composer.json");
        copy(__DIR__ . '/../fixtures/violinist-drupal-config.json', "$temp_folder/vendor/vendor/shared-violinist-drupal/violinist-drupal-config.json");
        copy(__DIR__ . '/../fixtures/level2.json', "$temp_folder/vendor/vendor/shared-violinist-common/composer.json");
        copy(__DIR__ . '/../fixtures/violinist-base-config.json', "$temp_folder/vendor/vendor/shared-violinist-common/violinist-base-config.json");

        // Create config
        $config = Config::createFromComposerPath("$temp_folder/composer.json");

        // Load expected result
        $expected = json_decode(file_get_contents(__DIR__ . '/../fixtures/violinist-expected-config.json'));

        // Compare final resolved config with expected. Except the things that
        // are the default config.
        $default_config = (new Config())->getDefaultConfig();
        $config_generated = $config->getConfig();
        // Remove the ones that are the same as in the default config.
        foreach ($config_generated as $key => $value) {
            if (isset($default_config->{$key}) && $default_config->{$key} === $value) {
                unset($config_generated->{$key});
            }
        }
        $this->assertEquals($expected, json_decode(json_encode($config->getConfig())));
    }
}
