<?php

namespace Violinist\Config\Matcher;

abstract class BaseMatcher implements MatcherInterface
{
    protected $config;
    protected $configName;

    public function __construct(\stdClass $config)
    {
        $this->config = $config;
    }

    public function applies() : bool
    {
        $rules = $this->getRelevantRules();
        return !empty($rules);
    }

    public function getRelevantRules() : array
    {
        if (empty($this->config)) {
            return [];
        }
        if (empty($this->configName)) {
            return [];
        }
        if (empty($this->config->matchRules)) {
            return [];
        }
        $rules_to_evaluate = [];
        foreach ($this->config->matchRules as $rule) {
            if (empty($rule->type)) {
                continue;
            }
            if ($rule->type === $this->configName) {
                $rules_to_evaluate[] = $rule;
            }
        }
        return $rules_to_evaluate;
    }

    public function match(string $name): bool
    {
        $rules_to_evaluate = $this->getRelevantRules();
        $matches = [];
        foreach ($rules_to_evaluate as $rule) {
            $matches[] = $this->matchRule($rule, $name);
            // If any matches have returned false, then we can return false.
            if (in_array(false, $matches)) {
                return false;
            }
        }
        return !empty($matches) && !in_array(false, $matches);
    }
}
