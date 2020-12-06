<?php namespace Sheba\Portals;

class ArticlePortals extends Portals
{
    const SMANAGER_FAQ_FOR_SHEBA_USER = 'smanager-faq-for-sheba-user';
    const SBUSINESS_FAQ_FOR_SHEBA_USER = 'sbusiness-faq-for-sheba-user';
    const SBONDHU_FAQ_FOR_SHEBA_USER = 'sbondhu-faq-for-sheba-user';
    const HR_AND_ADMIN_FAQ_FOR_SHEBA_USER = 'hr-and-admin-faq-for-sheba-user';
    const SHEBA_FAQ_FOR_SHEBA_USER = 'sheba-faq-for-sheba-user';
    const BUSINESS_APP = 'business-app';
    const BUSINESS_WEB = 'business-portal';

    public static function getReadableName($portal)
    {
        return self::getReadableNameForAllPortals()[$portal];
    }

    public static function getReadableNameForAllPortals()
    {
        return [
            self::ADMIN         => 'Customer Experience',
            self::PARTNER_APP   => 'Manager App',
            self::PARTNER_WEB   => 'Partner Portal',
            self::CUSTOMER_APP  => 'Customer App',
            self::CUSTOMER_WEB  => 'Customer Portal',
            self::RESOURCE_APP  => 'Resource App',
            self::RESOURCE_WEB  => 'Resource Portal',
            self::BONDHU_APP    => 'Bondhu App',
            self::BONDHU_WEB    => 'Bondhu Portal',
            self::BUSINESS_APP  => 'Business App',
            self::BUSINESS_WEB  => 'business Portal',
            self::SMANAGER_FAQ_FOR_SHEBA_USER => 'Smanager faq for sheba user',
            self::SBUSINESS_FAQ_FOR_SHEBA_USER => 'Sbusiness faq for sheba user',
            self::SBONDHU_FAQ_FOR_SHEBA_USER => 'Sbondhu faq for sheba user',
            self::HR_AND_ADMIN_FAQ_FOR_SHEBA_USER => 'Hr and admin faq for sheba user',
            self::SHEBA_FAQ_FOR_SHEBA_USER => 'Sheba faq for sheba user',
        ];
    }
}
