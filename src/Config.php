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
            'assignees' => [],
            'allow_updates_beyond_constraint' => 1,
            'one_pull_request_per_package' => 0,
            'timeframe_disallowed' => 0,
            'timezone' => '+0000',
            'update_with_dependencies' => 1,
        ];
    }

    public static function createFromComposerData($data)
    {
        $instance = new self();
        if (empty($data->extra->violinist)) {
            return;
        }
        $instance->setConfig($data->extra->violinist);
        return $instance;
    }

    public function setConfig($config)
    {
        foreach ($this->getDefaultConfig() as $key => $value) {
            if (isset($config->{$key})) {
                $this->config->{$key} = $config->{$key};
            }
        }
    }

    public function shouldUpdateDevDependencies()
    {
        return (bool) $this->config->update_dev_dependencies;
    }
}
