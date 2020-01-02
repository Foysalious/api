<?php namespace Sheba\Referral\Referrers;

use Illuminate\Support\Collection;
use Sheba\Referral\HasReferrals;
use Sheba\Referral\Referrer;
use Sheba\Referral\ReferrerInterface;

class Partner extends Referrer implements ReferrerInterface
{
    public function __construct(HasReferrals $referrer)
    {
        $this->referrer = $referrer;
    }

    function getReferrals(): Collection
    {
        $this->refers = $this->init()->selectRaw('`partners`.`id`,`partners`.`name`')->leftJoin('partner_usages_history', 'partners.id', '=', 'partner_usages_history.partner_id')->selectRaw('COUNT(DISTINCT(DATE(`partner_usages_history`.`created_at`))) as usages')->groupBy('partners.id')->get();
        foreach ($this->refers as $refer) {
            $refer['contact_number'] = $refer->getContactNumber();
            $refer['milestone']      = $this->getMilestoneForPartner($refer);
        }
        return $this->refers;
    }

    private function getMilestoneForPartner($partner)
    {

        $usage    = $partner->usage;
        $config   = config('partner.referral_steps');
        $earnings = 0;
        foreach ($config as $key => $configuration) {
            if ($configuration['nid_verification'])
                return [
                    'start'            => 0,
                    'end'              => 0,
                    'nid_verification' => true,
                    'future_earning'   => $earnings + $configuration['amount'],
                    'current_step'     => $config[$key]['step'],
                    'future_step'      => $config[$key + 1] ? $config[$key + 1]['step'] : null
                ];
            if ($configuration['duration'] > $usage) {
                return [
                    'start'            => (isset($config[$key - 1]) ? $config[$key - 1]['duration'] : 0),
                    'end'              => $configuration['duration'],
                    'nid_verification' => $configuration['nid_verification'],
                    'future_earning'   => $earnings + $configuration['amount'],
                    'current_step'     => (isset($config[$key - 1]) ? $config[$key - 1]['step'] : 'পেন্ডিং'),
                    'future_step'      => $config[$key]['step']
                ];
            }
            $earnings += $configuration['amount'];
        }
        return [
            'start'            => 0,
            'end'              => $config[0]['duration'],
            'nid_verification' => true,
            'future_earning'   => $config[0]['amount'],
            'current_step'     => 'পেন্ডিং',
            'future_step'      => $config[0]['step']
        ];
    }
}
