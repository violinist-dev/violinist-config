<?php

namespace Violinist\Config\Tests\VersionThree;

use PHPUnit\Framework\TestCase;
use Violinist\Config\Config;

class MergedConfigValidationTest extends NestedLevelExampleBase
{
    public function testFinalMergedOutputIsCorrect()
    {
        $config = $this->config;
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
