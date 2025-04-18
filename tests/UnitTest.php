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
     * Test the composer_outdated_flag option.
     *
     * @dataProvider getOutdatedFlag
     */
    public function testOutdatedFlag($filename, $expected_result)
    {
        $data = $this->createDataFromFixture($filename);
        self::assertEquals($expected_result, $data->getComposerOutdatedFlag());
    }

    /**
     * Test that getLabels returns the expected things.
     *
     * @dataProvider getLabelsConfig
     */
    public function testLabels($filename, $expected_result)
    {
        $this->runTestExpectedResultFromFixture($filename, $expected_result, 'getLabels');
    }

    /**
     * Test that getLabelsSecurity returns the expected things.
     *
     * @dataProvider getLabelsSecurityConfig
     */
    public function testLabelsSecurity($filename, $expected_result)
    {
        $this->runTestExpectedResultFromFixture($filename, $expected_result, 'getLabelsSecurity');
    }

    protected function runTestExpectedResultFromFixture($filename, $expected_result, $method)
    {
        $data = $this->createDataFromFixture($filename);
        self::assertEquals($expected_result, $data->{$method}());
    }

    /**
     * Test the different things we can set in always allow direct.
     *
     * @dataProvider getAlwaysAllowDirect
     */
    public function testAlwaysDirect($filename, $expected_result)
    {
        $data = $this->createDataFromFixture($filename);
        self::assertEquals($expected_result, $data->shouldAlwaysAllowDirect());
    }

    /**
     * Test the different things we can set in ignore platform.
     *
     * @dataProvider getIgnorePlatform
     */
    public function testIgnorePlatform($filename, $expected_result)
    {
        $data = $this->createDataFromFixture($filename);
        self::assertEquals($expected_result, $data->shouldIgnorePlatformRequirements());
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
    public function testTimeframeDisallowed($filename, $expected_result, $throws = false)
    {
        $data = $this->createDataFromFixture($filename);
        if ($throws) {
              $this->expectException(\InvalidArgumentException::class);
        }
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
     * Test the default branch config option.
     *
     * @dataProvider getDefaultBranchSec
     */
    public function testDefaultBranchSec($filename, $expected_result)
    {
        $data = $this->createDataFromFixture($filename);
        self::assertEquals($expected_result, $data->getDefaultBranchSecurity());
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
     * Test the automerge option.
     *
     * @dataProvider getAutoMerge
     */
    public function testAutoMerge($filename, $expected_result)
    {
        $data = $this->createDataFromFixture($filename);
        self::assertEquals($expected_result, $data->shouldAutoMerge());
    }

    /**
     * Test the automerge_security option.
     *
     * @dataProvider getAutoMergeSecurity
     */
    public function testAutoMergeSecurity($filename, $expected_result)
    {
        $data = $this->createDataFromFixture($filename);
        self::assertEquals($expected_result, $data->shouldAutoMerge(true));
    }

    /**
     * Test the automerge_method option.
     *
     * @dataProvider getAutoMergeMethod
     */
    public function testAutoMergeMethod($filename, $expected_result)
    {
        $data = $this->createDataFromFixture($filename);
        self::assertEquals($expected_result, $data->getAutomergeMethod());
    }

    /**
     * Test the automerge_method option.
     *
     * @dataProvider getAutoMergeMethodSecurity
     */
    public function testAutoMergeMethodSecurity($filename, $expected_result)
    {
        $data = $this->createDataFromFixture($filename);
        self::assertEquals($expected_result, $data->getAutomergeMethod(true));
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
     * Test the allow_security_updates_on_concurrent_limit config option.
     *
     * @dataProvider getAllowSecurityUpdatesOnConcurrentLimitDataProvider
     */
    public function testAllowSecurityUpdatesOnConcurrentLimit($filename, $expected_result)
    {
        $data = $this->createDataFromFixture($filename);
        self::assertEquals($expected_result, $data->shouldAllowSecurityUpdatesOnConcurrentLimit());
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

    public static function getOutdatedFlag() : array
    {
        return [
            [
                'empty.json',
                'minor',
            ],
            [
                'outdated_flag.json',
                'minor',
            ],
            [
                'outdated_flag1.json',
                'minor',
            ],
            [
                'outdated_flag2.json',
                'patch',
            ],
            [
                'outdated_flag3.json',
                'minor',
            ],
        ];
    }

    public static function getUpdateWithDeps()
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

    public static function getAutoMerge()
    {
        return [
            [
                'empty.json',
                false,
            ],
            [
                'automerge.json',
                true,
            ],
            [
                'automerge2.json',
                true,
            ],
            [
                'automerge3.json',
                true,
            ],
            [
                'automerge4.json',
                false,
            ],
            [
                'automerge5.json',
                false,
            ],
        ];
    }

    public static function getAutoMergeSecurity()
    {
        return [
            [
                'empty.json',
                false,
            ],
            [
                'automerge.json',
                true,
            ],
            [
                'automerge2.json',
                true,
            ],
            [
                'automerge3.json',
                true,
            ],
            [
                'automerge4.json',
                false,
            ],
            [
                'automerge5.json',
                false,
            ],
            [
                'automerge_security.json',
                true,
            ],
            [
                'automerge_security2.json',
                true,
            ],
            [
                'automerge_security3.json',
                true,
            ],
        ];
    }

    public static function getAutoMergeMethod()
    {
        return [
            [
                'empty.json',
                'merge',
            ],
            [
                'automerge_method.json',
                'merge',
            ],
            [
                'automerge_method2.json',
                'merge',
            ],
            [
                'automerge_method3.json',
                'merge',
            ],
            [
                'automerge_method4.json',
                'squash',
            ],
            [
                'automerge_method5.json',
                'rebase',
            ],
            [
                'automerge_method6.json',
                'merge',
            ],
            [
                'automerge_method_security.json',
                'merge',
            ],
            [
                'automerge_method_security2.json',
                'merge',
            ],
            [
                'automerge_method_security3.json',
                'merge',
            ],
            [
                'automerge_method_security4.json',
                'merge',
            ],
            [
                'automerge_method_security5.json',
                'merge',
            ],
            [
                'automerge_method_security6.json',
                'merge',
            ],
        ];
    }

    public static function getAutoMergeMethodSecurity()
    {
        return [
            [
                'empty.json',
                'merge',
            ],
            [
                'automerge_method.json',
                'merge',
            ],
            [
                'automerge_method2.json',
                'merge',
            ],
            [
                'automerge_method3.json',
                'merge',
            ],
            [
                'automerge_method4.json',
                'squash',
            ],
            [
                'automerge_method5.json',
                'rebase',
            ],
            [
                'automerge_method6.json',
                'merge',
            ],
            [
                'automerge_method_security.json',
                'merge',
            ],
            [
                'automerge_method_security2.json',
                'merge',
            ],
            [
                'automerge_method_security3.json',
                'merge',
            ],
            [
                'automerge_method_security4.json',
                'squash',
            ],
            [
                'automerge_method_security5.json',
                'rebase',
            ],
            [
                'automerge_method_security6.json',
                'merge',
            ],
        ];
    }

    public static function getAlwaysUpdateAll()
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
            ],
        ];
    }

    public static function getAllowUpdateDirect()
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
            [
                'allow_indirect2.json',
                true,
            ],
            [
                'allow_indirect3.json',
                false,
            ],
        ];
    }


    public static function getUpdatesBeyondConstraint()
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

    public static function getOnePrPerPackage()
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

    public static function getAssignees()
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
                    "test_user",
                ],
            ],
        ];
    }

    public static function getBranchPrefix()
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

    public static function getTimeframes()
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
                true,
            ],
        ];
    }

    public static function getTimezone()
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

    public static function getCommitMessage()
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

    public static function getLabelsConfig()
    {
        return [
            [
                'empty.json',
                [],
            ],
            [
                'labels.json',
                [],
            ],
            [
                'labels2.json',
                [],
            ],
            [
                'labels3.json',
                [],
            ],
            [
                'labels4.json',
                [],
            ],
            [
                'labels5.json',
                ["tag123"],
            ],
            [
                'labels6.json',
                ["tag123", "tag456"],
            ],
        ];
    }

    public static function getLabelsSecurityConfig()
    {
        return [
            [
                'empty.json',
                [],
            ],
            [
                'labels_security.json',
                [],
            ],
            [
                'labels_security2.json',
                [],
            ],
            [
                'labels_security3.json',
                [],
            ],
            [
                'labels_security4.json',
                [],
            ],
            [
                'labels_security5.json',
                ["tag123"],
            ],
            [
                'labels_security6.json',
                ["tag123", "tag456"],
            ],
        ];
    }

    public static function getHasConfig()
    {
        return [
            [
                'empty.json',
                'one_pull_request_per_package',
                false,
            ],
            [
                'empty.json',
                'bogus_option',
                false,
            ],
            [
                'check_only_direct_dependencies5.json',
                'check_only_direct_dependencies',
                true,
            ],
            [
                'bundled_packages4.json',
                'bundled_packages',
                true,
            ],
            [
                'bundled_packages4.json',
                'blocklist',
                false,
            ],
        ];
    }

    /**
     * A data provider.
     */
    public static function emptyConfigs()
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

    public static function getIgnorePlatform()
    {
        return [
            [
                'empty.json',
                false,
            ],
            [
                'ignore_platform.json',
                true,
            ],
            [
                'ignore_platform2.json',
                true,
            ],
            [
                'ignore_platform3.json',
                true,
            ],
            [
                'ignore_platform4.json',
                false,
            ],
            [
                'ignore_platform5.json',
                false,
            ],
        ];
    }

    public static function getAlwaysAllowDirect()
    {
        return [
            [
                'empty.json',
                false,
            ],
            [
                'always_allow_direct_dependencies.json',
                true,
            ],
            [
                'always_allow_direct_dependencies2.json',
                true,
            ],
            [
                'always_allow_direct_dependencies3.json',
                true,
            ],
            [
                'always_allow_direct_dependencies4.json',
                false,
            ],
            [
                'always_allow_direct_dependencies5.json',
                false,
            ],
        ];
    }

    public static function getRunScriptData()
    {
        return [
            [
                'run_scripts.json',
                false,
            ],
            [
                'run_scripts2.json',
                true,
            ],
            [
                'run_scripts3.json',
                false,
            ],
            [
                'run_scripts4.json',
                true,
            ],
            [
                'run_scripts5.json',
                false,
            ],
            [
                'run_scripts6.json',
                true,
            ],
            [
                'run_scripts7.json',
                true,
            ],
            [
                'run_scripts8.json',
                true,
            ],
        ];
    }

    public static function getBundledOptionsForGetter()
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
                    'psr/log',
                ],
            ],
            [
                'bundled_packages5.json',
                [],
            ],
            [
                'bundled_packages6.json',
                [],
            ],
        ];
    }

    public static function getBundledOptions()
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
                    "symfony/console",
                ],
            ],
            [
                'bundled_packages5.json',
                [],
                true,
            ],
            [
                'bundled_packages6.json',
                [],
                true,
            ],
        ];
    }

    public static function getAllowList()
    {
        return [
            [
                'allow_list.json',
                [],
            ],
            [
                'allow_list2.json',
                [
                    "vendor/package",
                ],
            ],
            [
                'allow_list3.json',
                [
                    "vendor/package",
                ],
            ],
            [
                'empty.json',
                [],
            ],
        ];
    }

    public static function getBlackList()
    {
        return [
            [
                'blocklist.json',
                [],
            ],
            [
                'blocklist2.json',
                [
                    "package1",
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
                    "vendor/*",
                ],
            ],
            [
                'blocklist5.json',
                [],
            ],
            [
                'blocklist6.json',
                [
                    "package1",
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
                    "vendor/*",
                ],
            ],
        ];
    }

    public static function getDefaultBranch()
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

    public static function getDefaultBranchSec()
    {
        return [
            [
                'empty.json',
                '',
            ],
            [
                'default_branch_security.json',
                '',
            ],
            [
                'default_branch_security2.json',
                'develop',
            ],
            [
                'default_branch_security3.json',
                'main',
            ],
            [
                'default_branch_security4.json',
                'develop',
            ],
            [
                'default_branch_security5.json',
                '',
            ],
            [
                'default_branch_security6.json',
                'main',
            ],
        ];
    }

    public static function getUpdateDevDependencies()
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

    public static function getSecurityUpdates()
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

    /**
     * Data provider for testAllowSecurityUpdatesOnConcurrentLimit.
     */
    public static function getAllowSecurityUpdatesOnConcurrentLimitDataProvider()
    {
        return [
            ['empty.json', false],
            ['allow_security_updates_on_concurrent_limit.json', true],
            ['allow_security_updates_on_concurrent_limit1.json', false],
        ];
    }

    public static function getOnlyDirectDependencies()
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
