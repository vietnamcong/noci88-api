<?php

namespace Core\Helpers;

use Jenssegers\Agent\Agent;

class Device
{
    /**
     * is mobile
     *
     * @return bool
     */
    public static function isMobile(): bool
    {
        $agent = new Agent();
        $isPhone = $agent->isPhone();

        if (!array_key_exists('HTTP_USER_AGENT', $_SERVER)) {
            return $isPhone;
        }

        $userAgent = $_SERVER['HTTP_USER_AGENT'];

        $isDoCoMo = preg_match('!^DoCoMo!', $userAgent);
        $isEzWeb = preg_match('!^KDDI-!', $userAgent) || preg_match('!^UP\.Browser!', $userAgent);
        $isSoftBank = preg_match('!^SoftBank!', $userAgent)
            || preg_match('!^Semulator!', $userAgent)
            || preg_match('!^Vodafone!', $userAgent)
            || preg_match('!^Vemulator!', $userAgent)
            || preg_match('!^MOT-!', $userAgent)
            || preg_match('!^MOTEMULATOR!', $userAgent)
            || preg_match('!^J-PHONE!', $userAgent)
            || preg_match('!^J-EMULATOR!', $userAgent);
        $isWillCom = preg_match('!^Mozilla/3\.0\((?:DDIPOCKET|WILLCOM);!', $userAgent);

        return $isPhone || $isDoCoMo || $isEzWeb || $isSoftBank || $isWillCom;
    }

    /**
     * is tablet
     *
     * @return bool
     */
    public static function isTablet(): bool
    {
        $agent = new Agent();
        return $agent->isTablet();
    }

    /**
     * @return string
     */
    public static function getDevice(): string
    {
        $agent = new Agent();
        $device = $agent->device();
        return !$device ? '' : strtolower($device);
    }

    /**
     * get Os
     *
     * @return string
     */
    public static function getOs(): string
    {
        $agent = new Agent();
        $os = $agent->platform();
        return !$os ? '' : strtolower($os);
    }

    /**
     * get browser
     *
     * @return string
     */
    public static function getBrowser(): string
    {
        $agent = new Agent();
        $browser = $agent->browser();
        return !$browser ? '' : strtolower($browser);
    }
}
