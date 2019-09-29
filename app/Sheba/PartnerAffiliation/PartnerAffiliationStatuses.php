<?php namespace Sheba\PartnerAffiliation;

class PartnerAffiliationStatuses
{
    public static $rejected = "rejected";
    public static $successful = "successful";
    public static $pending = "pending";

    public static function get($key = null)
    {
        $statuses = constants('PARTNER_AFFILIATIONS_STATUSES');
        return $key ? $statuses[$key] : $statuses;
    }
}