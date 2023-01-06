<?php

namespace Violinist\Config;

class Config
{
    private $config;
    private $configOptionsSet = [];

    public function __construct()
    {
        $this->config = $this->getDefaultConfig();
    }

    public function getDefaultConfig()
    {
        return (object) [
            'always_update_all' => 0,
            'allow_list' => [],
            'update_dev_dependencies' => 1,
            'check_only_direct_dependencies' => 1,
            'bundled_packages' => (object) [],
            'blocklist' => [],
            'assignees' => [],
            'allow_updates_beyond_constraint' => 1,
            'one_pull_request_per_package' => 0,
            'timeframe_disallowed' => '',
            'timezone' => '+0000',
            'update_with_dependencies' => 1,
            'default_branch' => '',
            'run_scripts' => 1,
            'security_updates_only' => 0,
            'number_of_concurrent_updates' => 0,
            'branch_prefix' => '',
            'commit_message_convention' => '',
            'allow_update_indirect_with_direct' => 0,
            'automerge' => 0,
            'automerge_security' => 0,
            'tags' => [],
            'tags_security' => [],
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
                $this->configOptionsSet[$key] = true;
            }
        }
        // Also make sure to set the block list config from the deprecated part.
        $renamed_and_aliased = [
            'blacklist' => 'blocklist',
            'block_list' => 'blocklist',
            'allowlist' => 'allow_list',
        ] ;
        foreach ($renamed_and_aliased as $not_real => $real) {
            if (isset($config->{$not_real})) {
                $this->config->{$real} = $config->{$not_real};
            }
        }
    }

    public function getTags() : array
    {
        if (!is_array($this->config->tags)) {
            return [];
        }
        return $this->config->tags;
    }

    public function getTagsSecurity() : array 
    {
        if (!is_array($this->config->tags_security)) {
            return [];
        }
        return $this->config->tags_security;
    }

    public function hasConfigForKey($key)
    {
        return !empty($this->configOptionsSet[$key]);
    }

    public function shouldAutoMerge($is_security_update = false)
    {
        if (!$is_security_update) {
            // It's not a security update. Let's use the option found in the config.
            return (bool) $this->config->automerge;
        }
        if ($this->shouldAutoMergeSecurity()) {
            // Meaning we should automerge, no matter what the general automerge config says.
            return true;
        }
        // Fall back to using the actual option.
        return (bool) $this->config->automerge;
    }

    public function shouldAutoMergeSecurity()
    {
        return (bool) $this->config->automerge_security;
    }

    public function shouldUpdateIndirectWithDirect()
    {
        return (bool) $this->config->allow_update_indirect_with_direct;
    }

    public function shouldAlwaysUpdateAll()
    {
        return (bool) $this->config->always_update_all;
    }

    public function getTimeZone()
    {
        if (!is_string($this->config->timezone)) {
            return '+0000';
        }
        if (empty($this->config->timezone)) {
            return '+0000';
        }
        return $this->config->timezone;
    }

    public function getTimeFrameDisallowed()
    {
        if (!is_string($this->config->timeframe_disallowed)) {
            return '';
        }
        if (empty($this->config->timeframe_disallowed)) {
            return '';
        }
        $frame = $this->config->timeframe_disallowed;
        $length = count(explode('-', $frame));
        if ($length !== 2) {
            throw new \InvalidArgumentException('The timeframe should consist of two 24 hour format times separated by a dash ("-")');
        }
        return $this->config->timeframe_disallowed;
    }

    public function shouldUpdateWithDependencies()
    {
        return (bool) $this->config->update_with_dependencies;
    }

    public function shouldAllowUpdatesBeyondConstraint()
    {
        return (bool) $this->config->allow_updates_beyond_constraint;
    }

    public function shouldRunScripts()
    {
        return (bool) $this->config->run_scripts;
    }

    public function getPackagesWithBundles()
    {
        $with_bundles = [];
        if (!is_object($this->config->bundled_packages)) {
            return [];
        }
        foreach ($this->config->bundled_packages as $package => $bundle) {
            if (!is_array($bundle)) {
                continue;
            }
            $with_bundles[] = $package;
        }
        return $with_bundles;
    }

    public function getBundledPackagesForPackage($package_name)
    {
        if (!is_object($this->config->bundled_packages)) {
            return [];
        }
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

    public function getAssignees()
    {
        if (!is_array($this->config->assignees)) {
            return [];
        }

        return $this->config->assignees;
    }

    /**
     * Just an alias, since providers differ in their wording on this.
     */
    public function shouldUseOneMergeRequestPerPackage()
    {
        return $this->shouldUseOnePullRequestPerPackage();
    }

    public function shouldUseOnePullRequestPerPackage()
    {
        return (bool) $this->config->one_pull_request_per_package;
    }

    public function getBlockList()
    {
        if (!is_array($this->config->blocklist)) {
            return [];
        }

        return $this->config->blocklist;
    }

    public function getAllowList()
    {
        if (!is_array($this->config->allow_list)) {
            return [];
        }

        return $this->config->allow_list;
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

    public function getBranchPrefix()
    {
        if ($this->config->branch_prefix) {
            if (!is_string($this->config->branch_prefix)) {
                return '';
            }
            return (string) $this->config->branch_prefix;
        }
        return '';
    }

    public function getCommitMessageConvention()
    {
        if (!$this->config->commit_message_convention || !is_string($this->config->commit_message_convention)) {
            return '';
        }

        return $this->config->commit_message_convention;
    }
}
