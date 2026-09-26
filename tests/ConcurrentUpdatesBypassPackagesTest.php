<?php

namespace Violinist\Config\Tests;

use PHPUnit\Framework\TestCase;
use Violinist\Config\Config;

class ConcurrentUpdatesBypassPackagesTest extends TestCase
{
    public function testDefaultsToEmptyList(): void
    {
        $config = new Config();

        self::assertSame([], $config->getConcurrentUpdatesBypassPackages());
        self::assertFalse($config->shouldBypassConcurrentLimitForPackage('vendor/package1'));
    }

    public function testMatchesExactAndWildcardPackageNames(): void
    {
        $config = Config::createFromViolinistConfigJsonString(json_encode([
            'concurrent_updates_bypass_packages' => [
                'exact/package',
                'vendor/*',
                'prefix/package_*',
            ],
        ]));

        self::assertSame(
            ['exact/package', 'vendor/*', 'prefix/package_*'],
            $config->getConcurrentUpdatesBypassPackages()
        );
        self::assertTrue($config->shouldBypassConcurrentLimitForPackage('exact/package'));
        self::assertTrue($config->shouldBypassConcurrentLimitForPackage('vendor/anything'));
        self::assertTrue($config->shouldBypassConcurrentLimitForPackage('prefix/package_one'));
        self::assertFalse($config->shouldBypassConcurrentLimitForPackage('other/package'));
    }

    public function testInvalidConfigurationReturnsEmptyList(): void
    {
        $config = Config::createFromViolinistConfigJsonString(json_encode([
            'concurrent_updates_bypass_packages' => 'vendor/*',
        ]));

        self::assertSame([], $config->getConcurrentUpdatesBypassPackages());
        self::assertFalse($config->shouldBypassConcurrentLimitForPackage('vendor/package1'));
    }

    public function testInvalidListEntriesAreIgnored(): void
    {
        $config = Config::createFromViolinistConfigJsonString(json_encode([
            'concurrent_updates_bypass_packages' => [
                'vendor/*',
                '',
                123,
                false,
                null,
            ],
        ]));

        self::assertSame(['vendor/*'], $config->getConcurrentUpdatesBypassPackages());
        self::assertTrue($config->shouldBypassConcurrentLimitForPackage('vendor/package1'));
    }
}
