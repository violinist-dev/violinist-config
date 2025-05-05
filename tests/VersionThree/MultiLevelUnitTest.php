<?php

namespace Violinist\Config\Tests\VersionThree;

use PHPUnit\Framework\TestCase;
use Violinist\Config\Config;

class MultiLevelUnitTest extends TestCase
{
    public function testMultiLevel()
    {
        // Let's start by creating a temp directory.
        $temp_folder = sys_get_temp_dir() . '/' . uniqid();
        mkdir($temp_folder);
        $composer_data = (object) [
            'extra' => (object) [
                'violinist' => (object) [
                    'extends' => 'vendor/shared-violinist-drupal',
                ],
            ],
        ];
        $composer_path = $temp_folder . '/composer.json';
        file_put_contents($composer_path, json_encode($composer_data));
        // Now create the vendor directory, plus the couple of packages we are
        // faking here.
        mkdir("$temp_folder/vendor");
        mkdir("$temp_folder/vendor/vendor");
        mkdir("$temp_folder/vendor/vendor/shared-violinist-drupal");
        mkdir("$temp_folder/vendor/vendor/shared-violinist-common");
        // Let's create the composer.json file for the shared-violinist-drupal.
        $filename = 'level1.json';
        $data = file_get_contents(__DIR__ . '/../fixtures/' . $filename);
        // Place it directly in the package dir for the first one and name it
        // composer.json
        file_put_contents("$temp_folder/vendor/vendor/shared-violinist-drupal/composer.json", $data);
        // We also want the file called violinist-drupal-config.json to be in
        // the same dir.
        $filename = 'violinist-drupal-config.json';
        $data = file_get_contents(__DIR__ . '/../fixtures/' . $filename);
        // Place it directly in the package dir for the first one with the same
        // filename.
        file_put_contents("$temp_folder/vendor/vendor/shared-violinist-drupal/$filename", $data);
        // Now we want to create the composer.json file for the second one. It's
        // called level2.json for our tests.
        $filename = 'level2.json';
        $data = file_get_contents(__DIR__ . '/../fixtures/' . $filename);
        // Place it directly in the package dir for the second one and name it
        // composer.json
        file_put_contents("$temp_folder/vendor/vendor/shared-violinist-common/composer.json", $data);
        // We also want the file called violinist-base-config.json to be in
        // there.
        $filename = 'violinist-base-config.json';
        $data = file_get_contents(__DIR__ . '/../fixtures/' . $filename);
        // Place it directly in the package dir for the second one with the same
        // filename.
        file_put_contents("$temp_folder/vendor/vendor/shared-violinist-common/$filename", $data);
        // Alright let's retrieve the config from the top level.
        $config = Config::createFromComposerPath($composer_path);
        self::assertEquals($config->getBranchPrefix(), 'violinist/');
        // Let's retrieve one from level 2.
        $block_list = $config->getBlockList();
        self::assertTrue(in_array('drupal/core', $block_list));
        // Now let's overwrite them on the top level.
        $composer_data = (object) [
            'extra' => (object) [
                'violinist' => (object) [
                    'branch_prefix' => 'test',
                    'blocklist' => ['drupal/core-o-rama', 'drupal/core-8'],
                    'extends' => 'vendor/shared-violinist-drupal',
                ],
            ],
        ];
        file_put_contents($composer_path, json_encode($composer_data));
        $config = Config::createFromComposerPath($composer_path);
        // Let's check the block list again.
        $block_list = $config->getBlockList();
        self::assertTrue(in_array('drupal/core-o-rama', $block_list));
        self::assertTrue(in_array('drupal/core-8', $block_list));
        self::assertFalse(in_array('drupal/core', $block_list));
        // Let's check the branch prefix.
        self::assertEquals($config->getBranchPrefix(), 'test');
    }
}
