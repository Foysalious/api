<?php namespace Sheba\AppVersion;

use Sheba\Helpers\Http\ShebaRequestHeader;
use Sheba\Portals\Portals;

class AppBuilder
{
    /**
     * @param ShebaRequestHeader $header
     * @return App|null
     */
    public static function buildFromHeader(ShebaRequestHeader $header)
    {
        return static::buildFromPortalName($header->getPortalName(), $header->getPlatformName(), $header->getVersionCode());
    }

    /**
     * @param $portal
     * @param null $platform
     * @param null $version
     * @return App|null
     */
    public static function buildFromPortalName($portal, $platform = null, $version = null)
    {
        if (Portals::isNotApp($portal)) return null;
        $platform = $platform ?: Apps::ANDROID_PLATFORM;

        /** @var App $app */
        $app = app(App::class);

        $app->setPortalName($portal)->setPlatformName($platform);
        if ($version) $app->setVersionName($version);

        return $app;
    }

    /**
     * @param $app_name
     * @param null $version
     * @return App|null
     */
    public static function buildFromAppName($app_name, $version = null)
    {
        $names = self::getPortalAndPlatformFromName($app_name);
        return static::buildFromPortalName($names['portal'], $names['platform'], $version);
    }

    /**
     * @param $name
     * @return array
     */
    public static function getPortalAndPlatformFromName($name): array
    {
        $portal = null;
        $platform = null;

        if ($name == Apps::CUSTOMER_APP_IOS) {
            $portal = Portals::CUSTOMER_APP;
            $platform = Apps::IOS_PLATFORM;
        } elseif ($name == Apps::CUSTOMER_APP_ANDROID) {
            $portal = Portals::CUSTOMER_APP;
        } elseif ($name == Apps::EMPLOYEE_APP_IOS) {
            $portal = Portals::EMPLOYEE_APP;
            $platform = Apps::IOS_PLATFORM;
        } elseif ($name == Apps::BONDHU_APP_IOS) {
            $portal = Portals::BONDHU_APP;
            $platform = Apps::IOS_PLATFORM;
        } elseif ($name == Apps::EMPLOYEE_APP_ANDROID) {
            $portal = Portals::EMPLOYEE_APP;
        } elseif ($name == Apps::RESOURCE_APP_IOS) {
            $portal = Portals::RESOURCE_APP;
            $platform = Apps::IOS_PLATFORM;
        } elseif ($name == Apps::RESOURCE_APP_ANDROID) {
            $portal = Portals::RESOURCE_APP;
        } elseif ($name == Apps::BONDHU_APP_ANDROID) {
            $portal = Portals::BONDHU_APP;
        } elseif ($name == Apps::MANAGER_APP_ANDROID) {
            $portal = Portals::PARTNER_APP;
        }

        return ['portal' => $portal, 'platform' => $platform];
    }

    /**
     * @param $portal
     * @param null $platform
     * @return string|null
     */
    public static function getNameFromPortalAndPlatform($portal, $platform = null)
    {
        $is_ios = ($platform == Apps::IOS_PLATFORM);

        if ($portal == Portals::CUSTOMER_APP) {
            if ($is_ios) return Apps::CUSTOMER_APP_IOS;
            return Apps::CUSTOMER_APP_ANDROID;
        }

        if ($portal == Portals::EMPLOYEE_APP) {
            if ($is_ios) return Apps::EMPLOYEE_APP_IOS;
            return Apps::EMPLOYEE_APP_ANDROID;
        }

        if ($portal == Portals::RESOURCE_APP) {
            if ($is_ios) return Apps::RESOURCE_APP_IOS;
            return Apps::RESOURCE_APP_ANDROID;
        }

        if ($portal == Portals::BONDHU_APP) {
            if ($is_ios) return Apps::BONDHU_APP_IOS;
            return Apps::BONDHU_APP_ANDROID;
        }

        if ($portal == Portals::PARTNER_APP) return Apps::MANAGER_APP_ANDROID;

        return null;
    }
}
