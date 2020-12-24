<?php namespace Sheba\Help;

use Sheba\Helpers\ConstGetter;

class UserPortalMapper
{
    use ConstGetter;

    const ADMIN = 'admin-portal';
    const BUSINESS = 'business-portal';
    const EMPLOYEE = 'digigo-portal';

    public static function getPortalByUser($user)
    {
        return self::getWithKeys()[strtoupper($user)];
    }
}
