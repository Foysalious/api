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
    const CLI = 'automatic';
}
