<?php namespace Sheba\PartnerAffiliation;

class PartnerAffiliationRejectReasons
{
    public static function get($key = null)
    {
        $statuses = constants('PARTNER_AFFILIATIONS_REJECT_REASONS');
        return $key ? $statuses[$key] : $statuses;
    }

    public static function fake()
    {
        return constants('PARTNER_AFFILIATIONS_FAKE_REJECT_REASONS');
    }

    public static function getWithIsFakeFlag()
    {
        $reasons = self::get();
        $fake_reasons = self::fake();
        foreach ($reasons as $key => $reason) {
            $reasons[$key] = ['name' => $reason, 'is_fake' => in_array($key, $fake_reasons)];
        }
        return $reasons;
    }

    public static function getKeys()
    {
        return array_keys(self::get());
    }
}