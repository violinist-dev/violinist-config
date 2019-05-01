<?php

namespace Violinist\Config;

class Config
{
    private $defaultConfig = (object) [
        'update_dev_dependencies' => true,
    ];

    private $config;

    public function __construct()
    {
        $this->config = $this->defaultConfig;
    }

    public static function createFromComposerData($data)
    {
        $instance = new self();
        if (empty($data->extra->violinist)) {
            return;
        }
        $instance->setConfig($data->extra->violinist);
    }

    public function setConfig($config)
    {
        $this->config = $config;
    }

    public function getUpdateDevDependencies()
    {
        return (bool) $this->config->update_dev_dependencies;
    }
}
