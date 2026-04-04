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

    public function testRuleDoesNotOverrideExistingConfig()
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
        self::assertFalse($config_from_rule->shouldUpdateDevDependencies());
    }

    public function testLaterRuleOverridesEarlierRuleForSamePackage()
    {
        // A catch-all rule sets security_updates_only for all matched packages.
        // A more specific rule after it can override back to the default for
        // specific packages, since rules can override each other (but not the
        // global config).
        $config_data = (object) [
            'rules' => [
                (object) [
                    'name' => 'Security only for vendor packages',
                    'matchRules' => [
                        (object) ['type' => 'names', 'values' => ['vendor/*']],
                    ],
                    'config' => (object) [
                        'security_updates_only' => 1,
                    ],
                ],
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

        $config_for_package_a = $config->getConfigForPackage('vendor/package-a');
        self::assertFalse($config_for_package_a->shouldOnlyUpdateSecurityUpdates());

        $config_for_package_b = $config->getConfigForPackage('vendor/package-b');
        self::assertFalse($config_for_package_b->shouldOnlyUpdateSecurityUpdates());

        $config_for_other = $config->getConfigForPackage('vendor/package-c');
        self::assertTrue($config_for_other->shouldOnlyUpdateSecurityUpdates());
    }

    public function testRuleDoesNotOverrideGlobalConfigForPackage()
    {
        $config_data = (object) [
            'security_updates_only' => 1,
            'rules' => [
                (object) [
                    'name' => 'Try to override security_updates_only',
                    'matchRules' => [
                        (object) ['type' => 'names', 'values' => ['vendor/*']],
                    ],
                    'config' => (object) [
                        'security_updates_only' => 0,
                    ],
                ],
            ],
        ];
        $config = Config::createFromViolinistConfig($config_data);

        $config_for_package = $config->getConfigForPackage('vendor/package-a');
        self::assertTrue($config_for_package->shouldOnlyUpdateSecurityUpdates());
    }

    public function testRuleDoesNotOverrideGlobalBundledPackages()
    {
        $config_data = (object) [
            'bundled_packages' => (object) ['psr/log' => ['symfony/console']],
            'rules' => [
                (object) [
                    'name' => 'Try to override bundled_packages',
                    'matchRules' => [
                        (object) ['type' => 'names', 'values' => ['psr/*']],
                    ],
                    'config' => (object) [
                        'bundled_packages' => (object) ['psr/log' => ['other/package']],
                    ],
                ],
            ],
        ];
        $config = Config::createFromViolinistConfig($config_data);

        $config_for_package = $config->getConfigForPackage('psr/log');
        self::assertEquals(['symfony/console'], $config_for_package->getBundledPackagesForPackage('psr/log'));
    }

    public function testRuleObjectDoesNotOverrideGlobalBundledPackages()
    {
        $config_data = (object) [
            'bundled_packages' => (object) ['psr/log' => ['symfony/console']],
        ];
        $config = Config::createFromViolinistConfig($config_data);
        $rule = (object) [
            'config' => (object) [
                'bundled_packages' => (object) ['psr/log' => ['other/package']],
            ],
        ];

        $config_from_rule = $config->getConfigForRuleObject($rule);
        self::assertEquals(['symfony/console'], $config_from_rule->getBundledPackagesForPackage('psr/log'));
    }
}
