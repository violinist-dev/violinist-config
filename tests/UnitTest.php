<?php

namespace Violinist\Config\Tests;

use PHPUnit\Framework\TestCase;
use Violinist\Config\Config;

class UnitTest extends TestCase
{
    /**
     * Test that things work like we want with empty config.
     *
     * @dataProvider emptyConfigs
     */
    public function testEmptyConfig($filename)
    {
        $data = $this->createDataFromFixture($filename);
        // Check some basic data, and the fact that it matches the default value.
        self::assertEquals($data->getDefaultBranch(), '');
        self::assertEquals($data->getNumberOfAllowedPrs(), 0);
    }

    /**
     * Test that we can get has config options.
     *
     * @dataProvider getHasConfig
     */
    public function testGetOptionSet($filename, $option, $expected_result)
    {
        $data = $this->createDataFromFixture($filename);
        self::assertEquals($data->hasConfigForKey($option), $expected_result);
    }

    /**
     * Test the different things we can set in run scripts, and what we expect from it.
     *
     * @dataProvider getRunScriptData
     */
    public function testRunScripts($filename, $expected_result)
    {
        $data = $this->createDataFromFixture($filename);
        self::assertEquals($expected_result, $data->shouldRunScripts());
    }

    /**
     * Test the different things about bundled packages.
     *
     * @dataProvider getBundledOptions
     */
    public function testBundledPackages($filename, $expected_result, $expect_exception = false)
    {
        $data = $this->createDataFromFixture($filename);
        if ($expect_exception) {
            $this->expectException(\Exception::class);
            $this->expectExceptionMessage('Found bundle for psr/log but the bundle was not an array');
        }
        self::assertEquals($expected_result, $data->getBundledPackagesForPackage('psr/log'));
    }

    /**
     * Test the different things about bundled packages.
     *
     * @dataProvider getBundledOptionsForGetter
     */
    public function testGetBundledPackages($filename, $expected_result)
    {
        $data = $this->createDataFromFixture($filename);
        self::assertEquals($expected_result, $data->getPackagesWithBundles());
    }

    /**
     * Test the blacklist config option.
     *
     * @dataProvider getBlackList
     */
    public function testBlackList($filename, $expected_result)
    {
        $data = $this->createDataFromFixture($filename);
        self::assertEquals($expected_result, $data->getBlackList());
        self::assertEquals($expected_result, $data->getBlockList());
    }

    /**
     * Test the allow list config option.
     *
     * @dataProvider getAllowList
     */
    public function testAllowList($filename, $expected_result)
    {
        $data = $this->createDataFromFixture($filename);
        self::assertEquals($expected_result, $data->getAllowList());
    }

    /**
     * Test the branch prefix config option.
     *
     * @dataProvider getBranchPrefix
     */
    public function testBranchPrefix($filename, $expected_result)
    {
        $data = $this->createDataFromFixture($filename);
        self::assertEquals($expected_result, $data->getBranchPrefix());
    }

    /**
     * Test the commit message convention config option.
     *
     * @dataProvider getCommitMessage
     */
    public function testCommitMessage($filename, $expected_result)
    {
        $data = $this->createDataFromFixture($filename);
        self::assertEquals($expected_result, $data->getCommitMessageConvention());
    }

    /**
     * Test the timezone option.
     *
     * @dataProvider getTimezone
     */
    public function testTimezone($filename, $expected_result)
    {
        $data = $this->createDataFromFixture($filename);
        self::assertEquals($expected_result, $data->getTimeZone());
    }

    /**
     * Test the timeframe_disallowed option.
     *
     * @dataProvider getTimeframes
     */
    public function testTimeframeDisallowed($filename, $expected_result)
    {
        $data = $this->createDataFromFixture($filename);
        self::assertEquals($expected_result, $data->getTimeFrameDisallowed());
    }

    /**
     * Test the default branch config option.
     *
     * @dataProvider getDefaultBranch
     */
    public function testDefaultBranch($filename, $expected_result)
    {
        $data = $this->createDataFromFixture($filename);
        self::assertEquals($expected_result, $data->getDefaultBranch());
    }

    /**
     * Test the update dev dependencies config option.
     *
     * @dataProvider getUpdateDevDependencies
     */
    public function testUpdateDevDependencies($filename, $expected_result)
    {
        $data = $this->createDataFromFixture($filename);
        self::assertEquals($expected_result, $data->shouldUpdateDevDependencies());
    }

    /**
     * Test the one pull request per package config option.
     *
     * @dataProvider getOnePrPerPackage
     */
    public function testOnePrPerPackage($filename, $expected_result)
    {
        $data = $this->createDataFromFixture($filename);
        self::assertEquals($expected_result, $data->shouldUseOnePullRequestPerPackage());
        self::assertEquals($expected_result, $data->shouldUseOneMergeRequestPerPackage());
    }

    /**
     * Test the update_with_dependencies option.
     *
     * @dataProvider getUpdateWithDeps
     */
    public function testUpdateWithDeps($filename, $expected_result)
    {
        $data = $this->createDataFromFixture($filename);
        self::assertEquals($expected_result, $data->shouldUpdateWithDependencies());
    }

    /**
     * Test the allow_updates_beyond_constraint option.
     *
     * @dataProvider getUpdatesBeyondConstraint
     */
    public function testUpdatesBeyondConstraint($filename, $expected_result)
    {
        $data = $this->createDataFromFixture($filename);
        self::assertEquals($expected_result, $data->shouldAllowUpdatesBeyondConstraint());
    }

    /**
     * Test the allow_update_direct_with_only_dependencies option.
     *
     * @dataProvider getAllowUpdateDirect
     */
    public function testAllowUpdateDirect($filename, $expected_result)
    {
        $data = $this->createDataFromFixture($filename);
        self::assertEquals($expected_result, $data->shouldUpdateIndirectWithDirect());
    }

    /**
     * Test the always_update_all option.
     *
     * @dataProvider getAlwaysUpdateAll
     */
    public function testAlwaysUpdateAll($filename, $expected_result)
    {
        $data = $this->createDataFromFixture($filename);
        self::assertEquals($expected_result, $data->shouldAlwaysUpdateAll());
    }

    /**
     * Test the security updates config option.
     *
     * @dataProvider getSecurityUpdates
     */
    public function testSecurityUpdates($filename, $expected_result)
    {
        $data = $this->createDataFromFixture($filename);
        self::assertEquals($expected_result, $data->shouldOnlyUpdateSecurityUpdates());
    }

    /**
     * Test the only direct dependencies config option.
     *
     * @dataProvider getOnlyDirectDependencies
     */
    public function testOnlyDirectDependencies($filename, $expected_result)
    {
        $data = $this->createDataFromFixture($filename);
        self::assertEquals($expected_result, $data->shouldCheckDirectOnly());
    }

    /**
     * Test the assignees part.
     *
     * @dataProvider getAssignees
     */
    public function testAssignees($filename, $expected_reesult)
    {
        $data = $this->createDataFromFixture($filename);
        self::assertEquals($expected_reesult, $data->getAssignees());
    }

    protected function createDataFromFixture($filename)
    {
        $file_contents = json_decode(file_get_contents(__DIR__ . '/fixtures/' . $filename));
        return Config::createFromComposerData($file_contents);
    }

    public function getUpdateWithDeps()
    {
        return [
            [
                'empty.json',
                true,
            ],
            [
                'update_with_dependencies.json',
                true,
            ],
            [
                'update_with_dependencies2.json',
                false,
            ],
            [
                'update_with_dependencies3.json',
                true,
            ],
            [
                'update_with_dependencies4.json',
                false,
            ],
        ];
    }

    public function getAlwaysUpdateAll()
    {
        return [
            [
                'empty.json',
                false,
            ],
            [
                'always_all.json',
                true,
            ],
            [
                'always_all2.json',
                false,
            ],
            [
                'always_all3.json',
                false,
            ],
            [
                'always_all4.json',
                true,
            ],
            [
                'always_all5.json',
                true,
            ]
        ];
    }

    public function getAllowUpdateDirect()
    {
        return [
            [
                'empty.json',
                false,
            ],
            [
                'allow_indirect.json',
                true,
            ],
        ];
    }


    public function getUpdatesBeyondConstraint()
    {
        return [
            [
                'empty.json',
                true,
            ],
            [
                'allow_updates_beyond_constraint.json',
                true,
            ],
            [
                'allow_updates_beyond_constraint2.json',
                false,
            ],
            [
                'allow_updates_beyond_constraint3.json',
                true,
            ],
            [
                'allow_updates_beyond_constraint4.json',
                false,
            ],
        ];
    }

    public function getOnePrPerPackage()
    {
        return [
            [
                'empty.json',
                false,
            ],
            [
                'one_pull_request_per_package.json',
                true,
            ],
            [
                'one_pull_request_per_package2.json',
                false,
            ],
            [
                'one_pull_request_per_package3.json',
                true,
            ],
            [
                'one_pull_request_per_package4.json',
                true,
            ],
        ];
    }

    public function getAssignees()
    {
        return [
            [
                'empty.json',
                [],
            ],
            [
                'assignees.json',
                [],
            ],
            [
                'assignees2.json',
                [],
            ],
            [
                'assignees3.json',
                [
                    "test_user"
                ],
            ],
        ];
    }

    public function getBranchPrefix()
    {
        return [
            [
                'prefix.json',
                '',
            ],
            [
                'prefix2.json',
                '',
            ],
            [
                'prefix3.json',
                '',
            ],
            [
                'prefix4.json',
                'my_prefix',
            ],
        ];
    }

    public function getTimeframes()
    {
        return [
            [
                'empty.json',
                '',
            ],
            [
                'timeframe_disallowed.json',
                '12-14',
            ],
            [
                'timeframe_disallowed2.json',
                '',
            ],
            [
                'timeframe_disallowed3.json',
                '',
            ],
            [
                'timeframe_disallowed4.json',
                'does not really work, but value allowed',
            ],
        ];
    }

    public function getTimezone()
    {
        return [
            [
                'empty.json',
                '+0000',
            ],
            [
                'timezone.json',
                '+0000',
            ],
            [
                'timezone2.json',
                '+0000',
            ],
            [
                'timezone3.json',
                '+0000',
            ],
            [
                'timezone4.json',
                'Europe/Berlin',
            ],
        ];
    }

    public function getCommitMessage()
    {
        return [
            [
                'empty.json',
                '',
            ],
            [
                'commit_message.json',
                '',
            ],
            [
                'commit_message2.json',
                '',
            ],
            [
                'commit_message3.json',
                'conventional',
            ],
        ];
    }

    public function getHasConfig()
    {
        return [
            [
                'empty.json',
                'one_pull_request_per_package',
                false
            ],
            [
                'empty.json',
                'bogus_option',
                false
            ],
            [
                'check_only_direct_dependencies5.json',
                'check_only_direct_dependencies',
                true
            ],
            [
                'bundled_packages4.json',
                'bundled_packages',
                true
            ],
            [
                'bundled_packages4.json',
                'blocklist',
                false
            ],
        ];
    }

    /**
     * A data provider.
     */
    public function emptyConfigs()
    {
        return [
            ['empty.json'],
            ['empty2.json'],
            ['empty3.json'],
            ['empty4.json'],
            ['empty5.json'],
            ['empty6.json'],
        ];
    }

    public function getRunScriptData()
    {
        return [
            [
                'run_scripts.json',
                false
            ],
            [
                'run_scripts2.json',
                true
            ],
            [
                'run_scripts3.json',
                false
            ],
            [
                'run_scripts4.json',
                true
            ],
            [
                'run_scripts5.json',
                false
            ],
            [
                'run_scripts6.json',
                true
            ],
            [
                'run_scripts7.json',
                true
            ],
            [
                'run_scripts8.json',
                true
            ]
        ];
    }

    public function getBundledOptionsForGetter()
    {
        return [
            [
                'empty.json',
                [],
            ],
            [
                'bundled_packages.json',
                [],
            ],
            [
                'bundled_packages2.json',
                [],
            ],
            [
                'bundled_packages3.json',
                [],
            ],
            [
                'bundled_packages4.json',
                [
                    'psr/log'
                ],
            ],
            [
                'bundled_packages5.json',
                [],
            ],
            [
                'bundled_packages6.json',
                [],
            ]
        ];
    }

    public function getBundledOptions()
    {
        return [
            [
                'bundled_packages.json',
                [],
            ],
            [
                'bundled_packages2.json',
                [],
            ],
            [
                'bundled_packages3.json',
                [],
            ],
            [
                'bundled_packages4.json',
                [
                    "symfony/console"
                ],
            ],
            [
                'bundled_packages5.json',
                [],
                true
            ],
            [
                'bundled_packages6.json',
                [],
                true
            ]
        ];
    }

    public function getAllowList()
    {
        return [
            [
                'allow_list.json',
                [],
            ],
            [
                'allow_list2.json',
                [
                    "vendor/package"
                ],
            ],
            [
                'allow_list3.json',
                [
                    "vendor/package"
                ],
            ],
            [
                'empty.json',
                [],
            ],
        ];
    }

    public function getBlackList()
    {
        return [
            [
                'blocklist.json',
                [],
            ],
            [
                'blocklist2.json',
                [
                    "package1"
                ],
            ],
            [
                'blocklist3.json',
                [],
            ],
            [
                'blocklist4.json',
                [
                    "package1",
                    "vendor/*"
                ],
            ],
            [
                'blocklist5.json',
                [],
            ],
            [
                'blocklist6.json',
                [
                    "package1"
                ],
            ],
            [
                'blocklist7.json',
                [],
            ],
            [
                'blocklist8.json',
                [
                    "package1",
                    "vendor/*"
                ],
            ],
        ];
    }

    public function getDefaultBranch()
    {
        return [
            [
                'empty.json',
                '',
            ],
            [
                'default_branch.json',
                '',
            ],
            [
                'default_branch2.json',
                'develop',
            ],
        ];
    }

    public function getUpdateDevDependencies()
    {
        return [
            [
                'update_dev_dependencies.json',
                true,
            ],
            [
                'update_dev_dependencies2.json',
                true,
            ],
            [
                'update_dev_dependencies3.json',
                true,
            ],
            [
                'update_dev_dependencies4.json',
                false,
            ],
            [
                'update_dev_dependencies5.json',
                true,
            ],
            [
                'update_dev_dependencies6.json',
                true,
            ],
            [
                'update_dev_dependencies7.json',
                true,
            ],
            [
                'empty.json',
                true,
            ],
        ];
    }

    public function getSecurityUpdates()
    {
        return [
            [
                'security_updates_only.json',
                true,
            ],
            [
                'security_updates_only2.json',
                true,
            ],
            [
                'security_updates_only3.json',
                true,
            ],
            [
                'security_updates_only4.json',
                false,
            ],
            [
                'security_updates_only5.json',
                true,
            ],
            [
                'security_updates_only6.json',
                true,
            ],
            [
                'security_updates_only7.json',
                true,
            ],
            [
                'empty.json',
                false,
            ],
        ];
    }

    public function getOnlyDirectDependencies()
    {
        return [
            [
                'check_only_direct_dependencies.json',
                true,
            ],
            [
                'check_only_direct_dependencies2.json',
                true,
            ],
            [
                'check_only_direct_dependencies3.json',
                true,
            ],
            [
                'check_only_direct_dependencies4.json',
                false,
            ],
            [
                'check_only_direct_dependencies5.json',
                true,
            ],
            [
                'check_only_direct_dependencies6.json',
                true,
            ],
            [
                'check_only_direct_dependencies7.json',
                true,
            ],
            [
                'empty.json',
                true,
            ],
        ];
    }
}
