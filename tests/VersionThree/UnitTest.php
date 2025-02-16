<?php

namespace Violinist\Config\Tests\VersionThree;

use PHPUnit\Framework\TestCase;
use Violinist\Config\Config;

class UnitTest extends TestCase
{
    /**
     * Test that things work as expected with some overrides.
     *
     * @dataProvider dataProviderOverridesConfigDrupalContrib
     */
    public function testOverridesConfigDrupalContrib($file, $expected)
    {
        // We are going to do this several times. One is with the actual file
        // passed in. One is with the "old" format (inside a composer.json
        // file), one is with the "new" format (inside a violinist-config.json
        // file), one is with a composer.json file, but passing a path to the
        // config from there. And then lastly one will be inside a vendor
        // folder and passed as a package name. Ah, and maybe one with a package
        // name and actual file. Phew. Let's start by getting the config
        // contents though.
        $file = __DIR__ . '/../fixtures/' . $file . '.json';
        $data = file_get_contents($file);
        // Let's test the new format first (this is the contents of the new
        // format).
        $config = Config::createFromViolinistConfigJsonString($data);
        self::assertConfigIsOverwrittenAndCorrect($config);
        // Now create a temporary composer.json file.
        $composer_data = (object) [
            'extra' => (object) [
                'violinist' => json_decode($data),
            ],
        ];
        $config = Config::createFromComposerData($composer_data);
        self::assertConfigIsOverwrittenAndCorrect($config);
        $temp_folder = sys_get_temp_dir() . '/' . uniqid();
        mkdir($temp_folder);
        $composer_data = (object) [
            'extra' => (object) [
                'violinist' => (object) [
                    'extends' => 'temp.json',
                ],
            ],
        ];
        $composer_path = $temp_folder . '/composer.json';
        file_put_contents($composer_path, json_encode($composer_data));
        file_put_contents($temp_folder . '/temp.json', $data);
        $config = Config::createFromComposerPath($composer_path);
        self::assertConfigIsOverwrittenAndCorrect($config);
        $temp_folder = sys_get_temp_dir() . '/' . uniqid();
        mkdir($temp_folder);
        mkdir("$temp_folder/vendor");
        mkdir("$temp_folder/vendor/vendor");
        mkdir("$temp_folder/vendor/vendor/package");
        $composer_data = (object) [
            'extra' => (object) [
                'violinist' => (object) [
                    'extends' => 'vendor/package',
                ],
            ],
        ];
        $composer_path = $temp_folder . '/composer.json';
        file_put_contents($composer_path, json_encode($composer_data));
        file_put_contents($temp_folder . '/vendor/vendor/package/' . Config::VIOLINIST_CONFIG_FILE, $data);
        $config = Config::createFromComposerPath($composer_path);
        self::assertConfigIsOverwrittenAndCorrect($config);
        $temp_folder = sys_get_temp_dir() . '/' . uniqid();
        mkdir($temp_folder);
        mkdir("$temp_folder/vendor");
        mkdir("$temp_folder/vendor/vendor");
        mkdir("$temp_folder/vendor/vendor/package");
        $composer_data = (object) [
            'extra' => (object) [
                'violinist' => (object) [
                    'extends' => 'vendor/package/clever-person.json',
                ],
            ],
        ];
        $composer_path = $temp_folder . '/composer.json';
        file_put_contents($composer_path, json_encode($composer_data));
        file_put_contents($temp_folder . '/vendor/vendor/package/clever-person.json', $data);
        $config = Config::createFromComposerPath($composer_path);
        self::assertConfigIsOverwrittenAndCorrect($config);
    }

    public static function assertConfigIsOverwrittenAndCorrect(Config $config)
    {
        self::assertEquals(true, $config->shouldUpdateDevDependencies());
        $config_for_package = $config->getConfigForPackage('drupal/metatag');
        self::assertEquals(true, $config->shouldUpdateDevDependencies());
        self::assertEquals(false, $config_for_package->shouldUpdateDevDependencies());
    }

    public static function dataProviderOverridesConfigDrupalContrib()
    {
        return [
            [
                'file' => 'violinist-test-drupal-config',
                'expected' => [
                    'drupal_contrib' => [
                        'some' => 'override',
                    ],
                ],
            ],
        ];
    }
}
