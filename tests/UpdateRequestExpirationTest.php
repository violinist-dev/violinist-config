<?php

namespace Violinist\Config\Tests;

use PHPUnit\Framework\TestCase;
use Violinist\Config\Config;

class UpdateRequestExpirationTest extends TestCase
{
    public function testDefaultsAreDisabled() : void
    {
        $config = new Config();

        self::assertSame('', $config->getMaximumUpdateRequestAge());
        self::assertSame('', $config->getExpiredUpdateRequestCooldown());
    }

    public function testMaximumUpdateRequestAgeCanBeConfigured() : void
    {
        $config = Config::createFromViolinistConfig((object) [
            'maximum_update_request_age' => '8w',
        ]);

        self::assertSame('8w', $config->getMaximumUpdateRequestAge());
    }

    public function testCooldownDefaultsToTwiceMaximumAge() : void
    {
        $config = Config::createFromViolinistConfig((object) [
            'maximum_update_request_age' => '8w',
        ]);

        self::assertSame('16w', $config->getExpiredUpdateRequestCooldown());
    }

    public function testCooldownCanBeConfiguredExplicitly() : void
    {
        $config = Config::createFromViolinistConfig((object) [
            'maximum_update_request_age' => '8w',
            'expired_update_request_cooldown' => '4w',
        ]);

        self::assertSame('4w', $config->getExpiredUpdateRequestCooldown());
    }

    public function testDaysWeeksAndMonthsAreSupported() : void
    {
        $days = Config::createFromViolinistConfig((object) [
            'maximum_update_request_age' => '14d',
        ]);
        $weeks = Config::createFromViolinistConfig((object) [
            'maximum_update_request_age' => '8w',
        ]);
        $months = Config::createFromViolinistConfig((object) [
            'maximum_update_request_age' => '3m',
        ]);

        self::assertSame('14d', $days->getMaximumUpdateRequestAge());
        self::assertSame('28d', $days->getExpiredUpdateRequestCooldown());
        self::assertSame('8w', $weeks->getMaximumUpdateRequestAge());
        self::assertSame('16w', $weeks->getExpiredUpdateRequestCooldown());
        self::assertSame('3m', $months->getMaximumUpdateRequestAge());
        self::assertSame('6m', $months->getExpiredUpdateRequestCooldown());
    }

    public function testInvalidMaximumAgeIsIgnored() : void
    {
        $config = Config::createFromViolinistConfig((object) [
            'maximum_update_request_age' => '8 weeks',
        ]);

        self::assertSame('', $config->getMaximumUpdateRequestAge());
        self::assertSame('', $config->getExpiredUpdateRequestCooldown());
    }

    public function testInvalidCooldownFallsBackToDerivedValue() : void
    {
        $config = Config::createFromViolinistConfig((object) [
            'maximum_update_request_age' => '8w',
            'expired_update_request_cooldown' => 'later',
        ]);

        self::assertSame('16w', $config->getExpiredUpdateRequestCooldown());
    }

    public function testZeroDurationIsIgnored() : void
    {
        $config = Config::createFromViolinistConfig((object) [
            'maximum_update_request_age' => '0w',
            'expired_update_request_cooldown' => '0d',
        ]);

        self::assertSame('', $config->getMaximumUpdateRequestAge());
        self::assertSame('', $config->getExpiredUpdateRequestCooldown());
    }

    public function testNonStringDurationsAreIgnored() : void
    {
        $config = Config::createFromViolinistConfig((object) [
            'maximum_update_request_age' => 8,
            'expired_update_request_cooldown' => false,
        ]);

        self::assertSame('', $config->getMaximumUpdateRequestAge());
        self::assertSame('', $config->getExpiredUpdateRequestCooldown());
    }

    public function testNonStringCooldownFallsBackToDerivedValue() : void
    {
        $config = Config::createFromViolinistConfig((object) [
            'maximum_update_request_age' => '8w',
            'expired_update_request_cooldown' => 16,
        ]);

        self::assertSame('16w', $config->getExpiredUpdateRequestCooldown());
    }
}
