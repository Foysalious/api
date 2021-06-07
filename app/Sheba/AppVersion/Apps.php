<?php namespace Sheba\AppVersion;

use Sheba\Helpers\ConstGetter;

class Apps
{
    use ConstGetter;

    const CUSTOMER_APP_ANDROID = 'customer_app_android';
    const CUSTOMER_APP_IOS = 'customer_app_ios';
    const MANAGER_APP_ANDROID = 'manager_app_android';
    const RESOURCE_APP_ANDROID = 'resource_app_android';
    const RESOURCE_APP_IOS = 'resource_app_ios';
    const BONDHU_APP_ANDROID = 'bondhu_app_android';
    const BONDHU_APP_IOS = 'bondhu_app_ios';
    const RIDER_APP_ANDROID = 'rider_app_android';
    const EMPLOYEE_APP_ANDROID = 'employee_app_android';
    const EMPLOYEE_APP_IOS = 'employee_app_ios';

    const CUSTOMER_MARKET_NAME = 'Sheba.XYZ';
    const MANAGER_MARKET_NAME = 'sManager';
    const RESOURCE_MARKET_NAME = 'sPro';
    const BONDHU_MARKET_NAME = 'sBondhu';
    const RIDER_MARKET_NAME = 'sDelivery';
    const EMPLOYEE_MARKET_NAME = 'DigiGO';

    const CUSTOMER_APP_ANDROID_PACKAGE_NAME = 'xyz.sheba.customersapp';
    const CUSTOMER_APP_IOS_PACKAGE_NAME = 'xyz.sheba.app';
    const MANAGER_APP_ANDROID_PACKAGE_NAME = 'xyz.sheba.managerapp';
    const RESOURCE_APP_ANDROID_PACKAGE_NAME = 'xyz.sheba.resource';
    const RESOURCE_APP_IOS_PACKAGE_NAME = 'xyz.sheba.spro';
    const BONDHU_APP_ANDROID_PACKAGE_NAME = 'xyz.sheba.bondhu';
    const BONDHU_APP_IOS_PACKAGE_NAME = 'xyz.sheba.bondhu.ios';
    const RIDER_APP_ANDROID_PACKAGE_NAME = 'xyz.sheba.logistic';
    const EMPLOYEE_APP_ANDROID_PACKAGE_NAME = 'xyz.sheba.emanager';
    const EMPLOYEE_APP_IOS_PACKAGE_NAME = 'xyz.sheba.emanager.ios';

    const ANDROID_PLATFORM = "android";
    const IOS_PLATFORM = "ios";


    /**
     * These tags are only considered as app, others are for reference only
     *
     * @return string[]
     */
    public static function getWithKeys(): array
    {
        return [
            'CUSTOMER_APP_ANDROID' => static::CUSTOMER_APP_ANDROID,
            'CUSTOMER_APP_IOS' => static::CUSTOMER_APP_IOS,
            'MANAGER_APP_ANDROID' => static::MANAGER_APP_ANDROID,
            'RESOURCE_APP_ANDROID' => static::RESOURCE_APP_ANDROID,
            'RESOURCE_APP_IOS' => static::RESOURCE_APP_IOS,
            'BONDHU_APP_ANDROID' => static::BONDHU_APP_ANDROID,
            'BONDHU_APP_IOS' => static::BONDHU_APP_IOS,
            'RIDER_APP_ANDROID' => static::RIDER_APP_ANDROID,
            'EMPLOYEE_APP_ANDROID' => static::EMPLOYEE_APP_ANDROID,
            'EMPLOYEE_APP_IOS' => static::EMPLOYEE_APP_IOS
        ];
    }

    public static function getMarketNames(): array
    {
        return [
            static::CUSTOMER_APP_ANDROID => static::CUSTOMER_MARKET_NAME,
            static::CUSTOMER_APP_IOS => static::CUSTOMER_MARKET_NAME,
            static::MANAGER_APP_ANDROID => static::MANAGER_MARKET_NAME,
            static::RESOURCE_APP_ANDROID => static::RESOURCE_MARKET_NAME,
            static::RESOURCE_APP_IOS => static::RESOURCE_MARKET_NAME,
            static::BONDHU_APP_ANDROID => static::BONDHU_MARKET_NAME,
            static::BONDHU_APP_IOS => static::BONDHU_MARKET_NAME,
            static::RIDER_APP_ANDROID => static::RIDER_MARKET_NAME,
            static::EMPLOYEE_APP_ANDROID => static::EMPLOYEE_MARKET_NAME,
            static::EMPLOYEE_APP_IOS => static::EMPLOYEE_MARKET_NAME,
        ];
    }

    public static function getPackageNames(): array
    {
        return [
            static::CUSTOMER_APP_ANDROID => static::CUSTOMER_APP_ANDROID_PACKAGE_NAME,
            static::CUSTOMER_APP_IOS => static::CUSTOMER_APP_IOS_PACKAGE_NAME,
            static::MANAGER_APP_ANDROID => static::MANAGER_APP_ANDROID_PACKAGE_NAME,
            static::RESOURCE_APP_ANDROID => static::RESOURCE_APP_ANDROID_PACKAGE_NAME,
            static::RESOURCE_APP_IOS => static::RESOURCE_APP_IOS_PACKAGE_NAME,
            static::BONDHU_APP_ANDROID => static::BONDHU_APP_ANDROID_PACKAGE_NAME,
            static::BONDHU_APP_IOS => static::BONDHU_APP_IOS_PACKAGE_NAME,
            static::RIDER_APP_ANDROID => static::RIDER_APP_ANDROID_PACKAGE_NAME,
            static::EMPLOYEE_APP_ANDROID => static::EMPLOYEE_APP_ANDROID_PACKAGE_NAME,
            static::EMPLOYEE_APP_IOS => static::EMPLOYEE_APP_IOS_PACKAGE_NAME,
        ];
    }
}
