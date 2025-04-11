<?php

namespace Violinist\Config\Tests\VersionThree;

use PHPUnit\Framework\TestCase;
use Violinist\Config\Config;

class InvalidDataExtendsTest extends TestCase
{
    public function testMultiLevelWithInvalidData()
    {
        // Let's start by creating a temp directory.
        $temp_folder = sys_get_temp_dir() . '/' . uniqid(__CLASS__, true);
        mkdir($temp_folder);
        $composer_data = (object) [
            'extra' => (object) [
                'violinist' => (object) [
                    'extends' => 'vendor/shared-violinist-drupal',
                    'branch_prefix' => 'test',
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
        // Let's create the composer.json file for the shared-violinist-drupal
        // and its going to be empty.
        file_put_contents("$temp_folder/vendor/vendor/shared-violinist-drupal/composer.json", '');
        $config = Config::createFromComposerPath($composer_path);
        self::assertEquals('test', $config->getBranchPrefix());
    }
}
