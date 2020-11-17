<?php namespace Sheba\Profile;

use Sheba\Helpers\ConstGetter;

class Avatars
{
    use ConstGetter;

    const CUSTOMER = "customer";
    const RESOURCE = "resource";
    const AFFILIATE = "affiliate";
    const MEMBER = "member";
    const BANK_USER = "bankUser";
    const USER = "user";
    const STRATEGIC_PARTNER_MEMBER = "strategicPartnerMember";

    /**
     * @param $type
     * @return string
     */
    public static function getModelName($type)
    {
        $model_name = ucfirst(camel_case($type));

        $namespace = 'App\\Models\\';

        if(lcfirst($type) == self::STRATEGIC_PARTNER_MEMBER) $namespace = "Sheba\\Dal\\StrategicPartnerMember\\";

        return $namespace . $model_name;
    }
}
