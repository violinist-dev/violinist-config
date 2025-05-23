<?php

namespace Violinist\Config;

class Config
{
    private $config;
    private $configOptionsSet = [];
    private $matcherFactory;
    private $extendsChain = [];
    private $extendsStorage;

    const VIOLINIST_CONFIG_FILE = 'violinist-config.json';

    public function __construct()
    {
        $this->config = $this->getDefaultConfig();
        $this->extendsStorage = new ExtendsStorage();
    }

    public function getExtendsStorage()
    {
        return $this->extendsStorage;
    }

    public function getReadableChainForExtendName(string $name) : string
    {
        $chain = [$name];
        while (true) {
            $has_parent = false;
            foreach ($this->extendsChain as $parent => $child) {
                if ($child === $name) {
                    $has_parent = true;
                    if ($parent === '<root>') {
                        break 2;
                    }
                    $chain[] = $parent;
                    $name = $parent;
                    break;
                }
            }
            if ($has_parent) {
                continue;
            }
            throw new \RuntimeException('Could not find the parent of ' . $name . ' in extends chain');
        }
        $chain = array_reverse($chain);
        $chain = array_map(function ($item) {
            return sprintf('"%s"', $item);
        }, $chain);
        return implode(' -> ', $chain);
    }

    public function getExtendNameForKey(string $key) : string
    {
        // First find all the ones that actually are setting this in config.
        $extend_names = [];
        foreach ($this->extendsStorage->getExtendItems() as $extend_name => $items) {
            foreach ($items as $item) {
                if ($item->getKey() === $key) {
                    $extend_names[] = $extend_name;
                }
            }
        }
        // Now we need to consult our chain to see if we can find one on the
        // lowest level.
        $extend_name = '';
        foreach ($extend_names as $extend_name) {
            // Let's see if we can find a parent for it.
            foreach ($this->extendsChain as $parent => $child) {
                if ($child === $extend_name) {
                    // This means we have a parent for it. Let's see if we can
                    // find the parent in the list of extend names.
                    if (in_array($parent, $extend_names)) {
                        // Surely not it. Since the child sets it, it can not be
                        // the parent. So remove it from the extend names array.
                        $extend_names = array_diff($extend_names, [$parent]);
                    }
                }
            }
        }
        if (count($extend_names) === 0) {
            return '';
        }
        // At this point, hopefully we will have a single extend name left in
        // the array. If not, we will just return the first one.
        return reset($extend_names);
    }

    public function getDefaultConfig()
    {
        return (object) [
            'always_update_all' => 0,
            'always_allow_direct_dependencies' => 0,
            'ignore_platform_requirements' => 0,
            'allow_list' => [],
            'update_dev_dependencies' => 1,
            'check_only_direct_dependencies' => 1,
            'composer_outdated_flag' => 'minor',
            'bundled_packages' => (object) [],
            'blocklist' => [],
            'assignees' => [],
            'allow_updates_beyond_constraint' => 1,
            'one_pull_request_per_package' => 0,
            'timeframe_disallowed' => '',
            'timezone' => '+0000',
            'update_with_dependencies' => 1,
            'default_branch' => '',
            'default_branch_security' => '',
            'run_scripts' => 1,
            'security_updates_only' => 0,
            'number_of_concurrent_updates' => 0,
            'allow_security_updates_on_concurrent_limit' => 0,
            'branch_prefix' => '',
            'commit_message_convention' => '',
            'allow_update_indirect_with_direct' => 0,
            'automerge' => 0,
            'automerge_security' => 0,
            'automerge_method' => 'merge',
            'automerge_method_security' => 'merge',
            'labels' => [],
            'labels_security' => [],
        ];
    }

    public static function createFromComposerPath(string $path)
    {
        if (!file_exists($path)) {
            throw new \InvalidArgumentException('The path provided does not contain a composer.json file');
        }
        $composer_data = json_decode(file_get_contents($path));
        return self::createFromComposerDataInPath($composer_data, $path);
    }

    public static function createFromComposerDataInPath(\stdClass $data, string $path, string $initial_path = '', $parent = '')
    {
        // First we need the actual thing from the composer data.
        $instance = self::createFromComposerData($data);
        $extra_data = (object) [];
        if (!empty($data->extra->violinist)) {
            $extra_data = $data->extra->violinist;
        }
        $instance = self::handleExtendFromInstanceAndData($instance, $extra_data, $path, $initial_path, $parent);
        return $instance;
    }

    public static function handleExtendFromInstanceAndData(Config $instance, $data, $path, $initial_path = '', $parent = '') : Config
    {
        if (!$initial_path) {
            $initial_path = dirname($path);
        }
        // Now, is there a thing on the path in extends? Is there even
        // "extends"?
        if (empty($data->extends)) {
            return $instance;
        }
        $extends = $data->extends;
        // Remove the filename part of the path.
        $directory = dirname($path);
        $extends_path = $directory . '/' . $extends;
        $potential_places = [
            $extends_path,
            sprintf('%s/vendor/%s/%s', $directory, $extends, self::VIOLINIST_CONFIG_FILE),
            sprintf('%s/vendor/%s/composer.json', $directory, $extends),
            sprintf('%s/vendor/%s', $directory, $extends),
        ];
        if ($initial_path) {
            $potential_places[] = sprintf('%s/vendor/%s/%s', $initial_path, $extends, self::VIOLINIST_CONFIG_FILE);
            $potential_places[] = "$initial_path/vendor/$extends/composer.json";
        }
        foreach ($potential_places as $potential_place) {
            if (file_exists($potential_place) && !is_dir($potential_place)) {
                $extends_data = json_decode(file_get_contents($potential_place));
                if (!$extends_data) {
                    continue;
                }
                $extends_instance = self::createFromViolinistConfigInPath($extends_data, $potential_place, $initial_path, $extends);
                if (strpos($potential_place, 'composer.json') !== false) {
                    // This is a composer.json file. Let's create it from that.
                    $extends_instance = self::createFromComposerDataInPath($extends_data, $potential_place, $initial_path, $extends);
                }
                // Now merge the two.
                $extends_key = $parent;
                if (!$extends_key) {
                    $extends_key = '<root>';
                }
                // Copy over the extends chain to the new instance.
                $instance->extendsChain = $extends_instance->extendsChain;
                $instance->extendsChain[$extends_key] = $extends;
                $instance->mergeConfig($extends_instance, $extends, $parent);
                break;
            }
        }
        return $instance;
    }

    public function getConfig()
    {
        return $this->config;
    }

    public static function createFromComposerData($data)
    {
        $instance = new self();
        if (!empty($data->extra->violinist)) {
            $instance->setConfig($data->extra->violinist);
        }
        return $instance;
    }

    public static function createFromViolinistConfigInPath($data, $file_path, $initial_path = '', $parent = '')
    {
        $instance = self::createFromViolinistConfig($data);
        $instance = self::handleExtendFromInstanceAndData($instance, $data, $file_path, $initial_path, $parent);
        return $instance;
    }

    public static function createFromViolinistConfig($data)
    {
        $instance = new self();
        $instance->setConfig($data);
        return $instance;
    }

    public static function createFromViolinistConfigJsonString(string $data)
    {
        $json_data = json_decode($data, false, 512, JSON_THROW_ON_ERROR);
        return self::createFromViolinistConfig($json_data);
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
        // Plus alternative spelling from allow list.
        $renamed_and_aliased = [
            'blacklist' => 'blocklist',
            'block_list' => 'blocklist',
            'allowlist' => 'allow_list',
        ];
        foreach ($renamed_and_aliased as $not_real => $real) {
            if (isset($config->{$not_real})) {
                $this->config->{$real} = $config->{$not_real};
            }
        }
        if (!empty($config->rules)) {
            $this->config->rules = $config->rules;
        }
    }

    public function getComposerOutdatedFlag() : string
    {
        if (empty($this->config->composer_outdated_flag)) {
            return 'minor';
        }
        $allowed_values = [
            'major',
            'minor',
            'patch',
        ];
        if (!in_array($this->config->composer_outdated_flag, $allowed_values)) {
            return 'minor';
        }
        return $this->config->composer_outdated_flag;
    }

    public function getLabels() : array
    {
        if (!is_array($this->config->labels)) {
            return [];
        }
        return $this->config->labels;
    }

    public function getLabelsSecurity() : array
    {
        if (!is_array($this->config->labels_security)) {
            return [];
        }
        return $this->config->labels_security;
    }

    public function shouldAlwaysAllowDirect() : bool
    {
        return (bool) $this->config->always_allow_direct_dependencies;
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

    public function getAutomergeMethod($is_security_update = false) : string
    {
        if (!$is_security_update) {
            return $this->getAutoMergeMethodWithFallback('automerge_method');
        }
        // Otherwise, let's see if it's even set in config. Otherwise this
        // should be set to the value (or fallback value) of the general
        // automerge method.
        if ($this->hasConfigForKey('automerge_method_security')) {
            return $this->getAutoMergeMethodWithFallback('automerge_method_security');
        }
        return $this->getAutoMergeMethodWithFallback('automerge_method');
    }

    protected function getAutoMergeMethodWithFallback($automerge_property) : string
    {
        if (!in_array($this->config->{$automerge_property}, [
            'merge',
            'rebase',
            'squash',
        ])
        ) {
            return 'merge';
        }
        return $this->config->{$automerge_property};
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

    public function shouldAllowSecurityUpdatesOnConcurrentLimit()
    {
        return (bool) $this->config->allow_security_updates_on_concurrent_limit;
    }

    public function shouldOnlyUpdateSecurityUpdates()
    {
        return (bool) $this->config->security_updates_only;
    }

    public function getDefaultBranchSecurity()
    {
        return $this->getDefaultBranch(true);
    }

    public function getDefaultBranch($is_security = false)
    {
        if ($is_security && !empty($this->config->default_branch_security)) {
            return $this->config->default_branch_security;
        }
        if ($is_security && empty($this->config->default_branch_security)) {
            return $this->getDefaultBranch();
        }
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

    public function shouldIgnorePlatformRequirements() : bool
    {
        return (bool) $this->config->ignore_platform_requirements;
    }

    public function getCommitMessageConvention()
    {
        if (!$this->config->commit_message_convention || !is_string($this->config->commit_message_convention)) {
            return '';
        }

        return $this->config->commit_message_convention;
    }

    public function getConfigForPackage(string $package_name) : self
    {
        $rules = $this->getRules();
        if (empty($rules)) {
            return $this;
        }
        $new_config = clone $this->config;
        foreach ($this->config->rules as $rule) {
            if (empty($rule->config)) {
                continue;
            }
            $matches = $this->getMatcherFactory()->hasMatches($rule, $package_name);
            if (!$matches) {
                continue;
            }
            // Then merge the config for this rule.
            $this->mergeConfigFromConfigObject($new_config, $rule->config);
        }
        return self::createFromViolinistConfig($new_config);
    }

    public function getRules() : array
    {
        if (!empty($this->config->rules)) {
            return $this->config->rules;
        }
        return [];
    }

    protected function mergeConfig(Config $other, $extends_name, $parent)
    {
        $keys_and_values_affected = $this->mergeConfigFromConfigObject($this->getConfig(), $other->getConfig());
        $this->extendsStorage->addExtendItems($other->getExtendsStorage()->getExtendItems());
        foreach ($keys_and_values_affected as $key => $value) {
            $this->configOptionsSet[$key] = true;
            $this->extendsStorage->addExtendItem(new ExtendsChainItem($extends_name, $key, $value));
        }
    }

    protected function mergeConfigFromConfigObject(\stdClass $config, \stdClass $other) : array
    {
        $affected = [];
        $default_config = $this->getDefaultConfig();
        foreach ($other as $key => $value) {
            // If the value corresponds to the default config, we don't need to
            // set it.
            if (isset($default_config->{$key}) && $default_config->{$key} === $value) {
                continue;
            }
            // This special case is because the default config is a stdclass,
            // and that will not pass the strict equal test. So let's just
            // loosen it up a bit for this specific case.
            if ($key === 'bundled_packages' && $default_config->{$key} == $value) {
                continue;
            }
            // If our option is set, but not set to the default, let's not merge
            // it.
            if (isset($default_config->{$key}) && isset($config->{$key})) {
                // Special case for bundled packages again.
                if ($key === 'bundled_packages') {
                    if ($config->{$key} != $default_config->{$key}) {
                        continue;
                    }
                } else {
                    if ($config->{$key} !== $default_config->{$key}) {
                        continue;
                    }
                }
            }
            $config->{$key} = $value;
            $affected[$key] = $value;
        }
        return $affected;
    }

    public function getMatcherFactory() : MatcherFactory
    {
        if (!$this->matcherFactory) {
            $this->matcherFactory = new MatcherFactory();
        }
        return $this->matcherFactory;
    }
}
