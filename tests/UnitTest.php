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
     * Test the blacklist config option.
     *
     * @dataProvider getBlackList
     */
    public function testBlackList($filename, $expected_result)
    {
        $data = $this->createDataFromFixture($filename);
        self::assertEquals($expected_result, $data->getBlackList());
    }

    protected function createDataFromFixture($filename)
    {
        $file_contents = json_decode(file_get_contents(__DIR__ . '/fixtures/' . $filename));
        return Config::createFromComposerData($file_contents);
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
        ];
    }
}
