<?php

namespace Violinist\Config\Tests;

use PHPUnit\Framework\TestCase;
use Violinist\Config\Config;

class StaticCreatorTest extends TestCase
{
    /**
     * Test that we can create from Composer path.
     *
     * This means a path to a composer.json.
     *
     * @dataProvider dataProviderCreateFromComposerPath
     */
    public function testcreateFromComposerPath($path, $expected, $expect_exception = false)
    {
        if ($expect_exception) {
            $this->expectExceptionMessage($expected);
        }
        $config = Config::createFromComposerPath($path);

        if (!$expect_exception) {
            self::assertEquals($expected, $config);
        }
    }

    public static function dataProviderCreateFromComposerPath()
    {
        return [
            [
                'path' => __DIR__ . '/fixtures/empty.json',
                'expected' => Config::createFromComposerData([]),
            ],
            [
                'path' => __DIR__ . '/fixtures/not-existing-in-assets.json',
                'expected' => 'The path provided does not contain a composer.json file',
                'expect_exception' => true,
            ],
        ];
    }
}
