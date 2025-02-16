<?php

namespace Violinist\Config\Matcher;

class NameMatcher extends BaseMatcher
{

    protected $configName = 'names';

    protected function matchRule($rule, string $name)
    {
        if (empty($rule->values)) {
            return false;
        }
        // Now match all of the values.
        $has_positive_match = false;
        foreach ($rule->values as $value) {
            // Check if the rule is a negation.
            if (substr($value, 0, 1) === '!') {
                $value = substr($value, 1);
                if (fnmatch($value, $name)) {
                    return false;
                }
            } else {
                if (fnmatch($value, $name)) {
                    $has_positive_match = true;
                }
            }
        }
        return $has_positive_match;
    }
}
