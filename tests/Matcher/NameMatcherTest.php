<?php

namespace Violinist\Config\Tests\Matcher;

use PHPUnit\Framework\TestCase;
use Violinist\Config\Matcher\NameMatcher;

class NameMatcherTest extends TestCase
{
    /**
     * Test variations of what we can do with the matcher.
     *
     * @dataProvider dataProviderRules
     */
    public function testRules($rule, string $name, $expected)
    {
        $matcher = new NameMatcher($rule);
        self::assertEquals($expected, $matcher->match($name));
    }

    public static function dataProviderRules()
    {
        return [
            [
                'rule' => (object) [
                    'matchRules' => [
                        (object) [
                            'type' => 'names',
                            'values' => [
                                'drupal/*',
                            ],
                        ],
                    ],
                ],
                'name' => 'drupal/core',
                'expected' => true,
            ],
            [
                'rule' => (object) [
                    'matchRules' => [
                        (object) [
                            'type' => 'names',
                            'values' => [
                                '!drupal/*',
                            ],
                        ],
                    ],
                ],
                'name' => 'drupal/core',
                'expected' => false,
            ],
            [
                'rule' => (object) [
                    'matchRules' => [
                        (object) [
                            'type' => 'names',
                            'values' => [
                                'drupal/*',
                                '!drupal/core',
                            ],
                        ],
                    ],
                ],
                'name' => 'drupal/core',
                'expected' => false,
            ],
            [
                'rule' => (object) [
                    'matchRules' => [
                        (object) [
                            'type' => 'names',
                            // Values are empty, so no matches, positive or
                            // negative.
                            'values' => [],
                        ],
                    ],
                ],
                'name' => 'drupal/metatag',
                'expected' => false,
            ],
        ];
    }
}