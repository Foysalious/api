<?php

use App\Models\Partner;
use Sheba\Partner\WaitingStatusProcessor;

if (!function_exists('isPartnerReadyToVerified')) {
    /**
     * @param $partner
     * @return bool
     */
    function isPartnerReadyToVerified($partner)
    {
        if (!($partner instanceof Partner)) {
            $partner = Partner::find($partner);
        }
        return (new WaitingStatusProcessor())->setPartner($partner)->isEligibleForWaiting();
    }
}
if (!function_exists('getMilestoneForPartner')) {
    function getMilestoneForPartner($usage)
    {
        $config   = config('partner.referral_steps');
        $earnings = 0;
        foreach ($config as $key => $configuration) {
            if ($configuration['nid_verification'])
                return [
                    'start'            => 0,
                    'end'              => 0,
                    'nid_verification' => true,
                    'future_earning'   => $earnings + $configuration['amount'],
                    'current_step'             => (isset($config[$key - 1]) ? $config[$key - 1]['step'] : 'পেন্ডিং')
                ];
            if ($configuration['duration'] > $usage) {
                return [
                    'start'            => (isset($config[$key - 1]) ? $config[$key - 1]['duration'] : 0),
                    'end'              => $configuration['duration'],
                    'nid_verification' => $configuration['nid_verification'],
                    'future_earning'   => $earnings + $configuration['amount'],
                    'current_step'             => (isset($config[$key - 1]) ? $config[$key - 1]['step'] : 'পেন্ডিং')
                ];
            }
            $earnings += $configuration['amount'];
        }
        return [
            'start'            => 0,
            'end'              => $config[0]['duration'],
            'nid_verification' => true,
            'future_earning'   => $config[0]['amount'],
            'current_step'             => 'পেন্ডিং'
        ];
    }
}
