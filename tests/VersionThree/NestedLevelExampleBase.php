<?php

namespace Violinist\Config\Tests\VersionThree;

use PHPUnit\Framework\TestCase;
use Violinist\Config\Config;

abstract class NestedLevelExampleBase extends TestCase
{
    /**
     * The config object.
     *
     * @var \Violinist\Config\Config
     */
    protected $config;

    public function setUp(): void
    {
        parent::setUp();
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
        $this->config = Config::createFromComposerPath("$temp_folder/composer.json");
    }
}