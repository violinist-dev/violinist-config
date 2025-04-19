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
}
