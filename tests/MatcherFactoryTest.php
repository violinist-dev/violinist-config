<?php

namespace Violinist\Config\Tests;

use PHPUnit\Framework\TestCase;
use Violinist\Config\MatcherFactory;

class MatcherFactoryTest extends TestCase
{
    public function testNoneApplies()
    {
        $rule = (object) [];
        $factory = new MatcherFactory();
        self::assertFalse($factory->hasMatches($rule, 'drupal/metatag'));
    }

    public function testNoneAppliesByNameBecauseEmptyType()
    {
        $rule = (object) [
            'matchRules' => [
                (object) [
                    'notType' => 'nonexisting_rule_name',
                ],
            ],
        ];
        $factory = new MatcherFactory();
        self::assertFalse($factory->hasMatches($rule, 'drupal/core'));
    }
}
