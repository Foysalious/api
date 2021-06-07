<?php namespace Sheba\Portals;

use Sheba\Helpers\ConstGetter;

class Portals
{
    use ConstGetter;

    const ADMIN = 'admin-portal';
    const PARTNER_WEB = 'partner-portal';
    const PARTNER_APP = 'manager-app';
    const CUSTOMER_APP = 'customer-app';
    const CUSTOMER_WEB = 'customer-portal';
    const RESOURCE_WEB = 'resource-portal';
    const RESOURCE_APP = 'resource-app';
    const BONDHU_APP = 'bondhu-app';
    const BONDHU_WEB = 'bondhu-portal';
    const BUSINESS_WEB = 'business-portal';
    const EMPLOYEE_APP = 'employee-app';
    const CLI = 'automatic';

    /**
     * @param $portal
     * @return string|null
     */
    public static function getUserTypeFromPortal($portal)
    {
        $types = [
            self::ADMIN => "user",
            self::PARTNER_WEB => "partner",
            self::PARTNER_APP => "partner",
            self::CUSTOMER_APP => "customer",
            self::CUSTOMER_WEB => "customer",
            self::RESOURCE_WEB => "resource",
            self::RESOURCE_APP => "resource",
            self::BONDHU_APP => "affiliate",
            self::BONDHU_WEB => "affiliate",
        ];

        if (!array_key_exists($portal, $types)) return null;

        return $types[$portal];
    }

    /**
     * @param $portal
     * @return bool
     */
    public static function isApp($portal): bool
    {
        return in_array($portal, [
            Portals::CUSTOMER_APP,
            Portals::EMPLOYEE_APP,
            Portals::RESOURCE_APP,
            Portals::BONDHU_APP,
            Portals::PARTNER_APP
        ]);
    }

    /**
     * @param $portal
     * @return bool
     */
    public static function isNotApp($portal): bool
    {
        return !self::isApp($portal);
    }
}
