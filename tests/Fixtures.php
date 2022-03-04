<?php

namespace Tests;

abstract class Fixtures
{
    /** @return string */
    public static function adSlotHash()
    {
        return '60220c073cc811ec941d0ab243175da9';
    }

    /** @return int */
    public static function adSlotId()
    {
        return 834557;
    }

    /** @return string */
    public static function campaignHash()
    {
        return 'fef81b06365911e7943622000a974651';
    }

    /** @return int */
    public static function lineItemId()
    {
        return 192431;
    }

    /** @return string */
    public static function lineItemHash()
    {
        return '00009758365a11e7943622000a974651';
    }

    /** @return string */
    public static function newsletterHash()
    {
        return '03fb20f13cc811ec941d0ab243175da9';
    }

    /** @return int */
    public static function newsletterId()
    {
        return 36116;
    }

    /** @return string */
    public static function publisherHash()
    {
        return 'b3e515b13cc711ec941d0ab243175da9';
    }
}
