<?php

namespace Violinist\Config\Matcher;

interface MatcherInterface
{
    public function applies(): bool;
    public function match(string $name): bool;
    protected function matchRule($rule, $name);
}
