<?php

namespace Violinist\Config;

use Violinist\Config\Matcher\NameMatcher;

class MatcherFactory
{
    public function getMatchers($rule) : array
    {
        return [
            new NameMatcher($rule),
        ];
    }

    public function hasMatches($rule, string $package_name)
    {
        $matchers = $this->getMatchers($rule);
        foreach ($matchers as $matcher) {
            if (!$matcher->applies()) {
                continue;
            }
            if ($matcher->match($package_name)) {
                return true;
            }
        }
        return false;
    }
}
