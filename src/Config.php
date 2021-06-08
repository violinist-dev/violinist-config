<?php

namespace Violinist\Config;

class Config
{
    private $config;

    public function __construct()
    {
        $this->config = $this->getDefaultConfig();
    }

    public function getDefaultConfig()
    {
        return (object) [
            'update_dev_dependencies' => 1,
            'check_only_direct_dependencies' => 1,
            'bundled_packages' => (object) [],
            'blocklist' => [],
            'assignees' => [],
            'allow_updates_beyond_constraint' => 1,
            'one_pull_request_per_package' => 0,
            'timeframe_disallowed' => 0,
            'timezone' => '+0000',
            'update_with_dependencies' => 1,
            'default_branch' => '',
            'run_scripts' => 1,
            'security_updates_only' => 0,
            'number_of_concurrent_updates' => 0,
        ];
    }

    public static function createFromComposerData($data)
    {
        $instance = new self();
        if (!empty($data->extra->violinist)) {
            $instance->setConfig($data->extra->violinist);
        }
        return $instance;
    }

    public function setConfig($config)
    {
        foreach ($this->getDefaultConfig() as $key => $value) {
            if (isset($config->{$key})) {
                $this->config->{$key} = $config->{$key};
            }
        }
        // Also make sure to set the block list config from the deprecated part.
        $renamed = [
            'blacklist' => 'blocklist',
        ] ;
        foreach ($renamed as $old => $new) {
            if (isset($config->{$old})) {
                $this->config->{$new} = $config->{$old};
            }
        }
    }

    public function shouldRunScripts()
    {
        return (bool) $this->config->run_scripts;
    }


    public function getBundledPackagesForPackage($package_name)
    {
        foreach ($this->config->bundled_packages as $package => $bundle) {
            if ($package === $package_name) {
                if (!is_array($bundle)) {
                    throw new \Exception('Found bundle for ' . $package . ' but the bundle was not an array');
                }
                return $bundle;
            }

        }
        return [];
    }

    public function getBlockList()
    {
        return $this->config->blocklist;
    }

    /**
     * @deprecated Use ::getBlockList instead.
     */
    public function getBlackList()
    {
        return $this->getBlockList();
    }

    public function shouldUpdateDevDependencies()
    {
        return (bool) $this->config->update_dev_dependencies;
    }

    public function getNumberOfAllowedPrs()
    {
        return (int) $this->config->number_of_concurrent_updates;
    }

    public function shouldOnlyUpdateSecurityUpdates()
    {
        return (bool) $this->config->security_updates_only;
    }

    public function getDefaultBranch()
    {
        if (empty($this->config->default_branch)) {
            return false;
        }
        return $this->config->default_branch;
    }

    public function shouldCheckDirectOnly()
    {
        return (bool) $this->config->check_only_direct_dependencies;
    }
}
