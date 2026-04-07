<?php

namespace Violinist\Config\Tests\VersionThree;

class OverrideVisibilityTest extends NestedLevelExampleBase
{
    public function testExtendChain()
    {
        $config = $this->config;
        $config_that_set_automerge_method = $config->getExtendNameForKey('automerge_method');
        $readable_chain_for_extend_name = $config->getReadableChainForExtendName($config_that_set_automerge_method);
        self::assertEquals('violinist-base-config.json', $config_that_set_automerge_method);
        self::assertEquals('"vendor/shared-violinist-drupal" -> "violinist-drupal-config.json" -> "vendor/shared-violinist-common" -> "violinist-base-config.json"', $readable_chain_for_extend_name);
    }

    public function testExtendChainNonExtended()
    {
        $config = $this->config;
        $config_that_set_key = $config->getExtendNameForKey('always_update_all');
        self::assertEquals('', $config_that_set_key);
    }

    public function testInvalidExtendName()
    {
        $config = $this->config;
        self::expectException(\RuntimeException::class);
        $readable = $config->getReadableChainForExtendName('invalid_key');
    }

    public function testRuleOverridingExtendsKeyResetsProvenance()
    {
        // automerge_method = 'squash' originates from violinist-base-config.json via extends.
        self::assertEquals('violinist-base-config.json', $this->config->getExtendNameForKey('automerge_method'));

        $this->config->setConfig((object) [
            'rules' => [
                (object) [
                    'matchRules' => [
                        (object) ['type' => 'names', 'values' => ['vendor/package-a']],
                    ],
                    'config' => (object) ['automerge_method' => 'merge'],
                ],
            ],
        ]);

        $config_for_package = $this->config->getConfigForPackage('vendor/package-a');

        // The rule overrode automerge_method; the extend file must no longer
        // be reported as its source on the package-specific config.
        self::assertEquals('', $config_for_package->getExtendNameForKey('automerge_method'));
        // The global config's provenance must be unaffected.
        self::assertEquals('violinist-base-config.json', $this->config->getExtendNameForKey('automerge_method'));
    }
}
