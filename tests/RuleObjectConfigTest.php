<?php

namespace Violinist\Config\Tests;

use PHPUnit\Framework\TestCase;
use Violinist\Config\Config;

class RuleObjectConfigTest extends TestCase
{
    public function testGetConfigForRuleObjectReturnsRuleConfig()
    {
        $config_data = json_decode(file_get_contents(__DIR__ . '/fixtures/violinist-test-drupal-config.json'));
        $config = Config::createFromViolinistConfig($config_data);
        $rule = $config_data->rules[0];

        $config_from_rule = $config->getConfigForRuleObject($rule);

        self::assertNotSame($config, $config_from_rule);
        self::assertTrue($config->shouldUpdateDevDependencies());
        self::assertFalse($config_from_rule->shouldUpdateDevDependencies());
    }

    public function testRuleWithoutConfigReturnsOriginalConfig()
    {
        $config = new Config();
        $rule = (object) [
            'name' => 'noop',
        ];

        $result = $config->getConfigForRuleObject($rule);

        self::assertSame($config, $result);
    }

    public function testRuleDoesOverrideExistingConfig()
    {
        $config = Config::createFromViolinistConfig((object) [
            'update_dev_dependencies' => 0,
        ]);
        $rule = (object) [
            'config' => (object) [
                'update_dev_dependencies' => 1,
            ],
        ];

        $config_from_rule = $config->getConfigForRuleObject($rule);

        self::assertNotSame($config, $config_from_rule);
        self::assertFalse($config->shouldUpdateDevDependencies());
        self::assertTrue($config_from_rule->shouldUpdateDevDependencies());
    }

    public function testRuleCanOverrideBackToDefault()
    {
        $config_data = (object) [
            'security_updates_only' => 1,
            'rules' => [
                (object) [
                    'name' => 'Always update these',
                    'matchRules' => [
                        (object) ['type' => 'names', 'values' => ['vendor/package-a', 'vendor/package-b']],
                    ],
                    'config' => (object) [
                        'security_updates_only' => 0,
                    ],
                ],
            ],
        ];
        $config = Config::createFromViolinistConfig($config_data);

        self::assertTrue($config->shouldOnlyUpdateSecurityUpdates());

        $config_for_package_a = $config->getConfigForPackage('vendor/package-a');
        self::assertFalse($config_for_package_a->shouldOnlyUpdateSecurityUpdates());

        $config_for_package_b = $config->getConfigForPackage('vendor/package-b');
        self::assertFalse($config_for_package_b->shouldOnlyUpdateSecurityUpdates());

        $config_for_other = $config->getConfigForPackage('vendor/package-c');
        self::assertTrue($config_for_other->shouldOnlyUpdateSecurityUpdates());
    }
}
