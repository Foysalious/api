<?php namespace Sheba\Referral\Referrers;

use App\Models\PartnerReferral;
use App\Models\Resource;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Sheba\ModificationFields;
use Sheba\Referral\Exceptions\AlreadyExistProfile;
use Sheba\Referral\Exceptions\AlreadyReferred;
use Sheba\Referral\HasReferrals;
use Sheba\Referral\Referrer;
use Sheba\Referral\ReferrerInterface;

class Partner extends Referrer implements ReferrerInterface
{
    use ModificationFields;
    private $config;

    public function __construct(HasReferrals $referrer)
    {
        $this->referrer = $referrer;
        $this->config   = config('partner.referral_steps');
    }

    function getReferrals(): Collection
    {
        $this->refers = $this->init()->select([
            'partner_referrals.resource_mobile',
            'partner_referrals.resource_name',
            'partner_referrals.partner_id',
            'partner_referrals.referred_partner_id',
            'ref.referrer_income',
            'ref.refer_level',
            'partner_referrals.id'
        ])->leftJoin('partners', 'partners.id', '=', 'partner_referrals.partner_id')->leftJoin('partners as ref', 'ref.id', '=', 'partner_referrals.referred_partner_id')->get();
        $all_referred = collect();
        foreach ($this->refers as $refer) {
            $reference                   = [];
            $reference['contact_number'] = $refer->refer ? $refer->refer->getContactNumber() : $refer->resource_mobile;
            $reference['milestone']      = $this->getMilestoneForPartner($refer);
            $reference['income']         = $refer->referrer_income ?: 0;
            $reference['name']           = $refer->refer ? $refer->refer->name : $refer->resource_name;
            $reference['usage']          = $refer->usages;
            $reference['id']             = $refer->id;
            $reference['level']          = $refer->refer_level;
            $reference['state']          = $reference['milestone']['current_state'];
            $all_referred->push($reference);
        }
        return $all_referred;
    }

    private function getMilestoneForPartner($partner_referral)
    {
        $config = $this->config;
        if ($partner_referral->refer) {
            $usage    = $partner_referral->refer ? $partner_referral->refer->usage()->selectRaw('COUNT(DISTINCT(DATE(`partner_usages_history`.`created_at`))) as usages')->groupBy('partner_usages_history.partner_id')->first() : 0;
            $usage    = !empty($usage) ? $usage->usages : 0;
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

    /**
     * @param Request $request
     * @throws AlreadyExistProfile
     * @throws AlreadyReferred
     */
    function store(Request $request)
    {
        $mobile = formatMobile($request->mobile);
        if ($this->validateStore($mobile)) {
            $this->setModifier($this->referrer);
            $this->referrer->referrals()->create($this->withCreateModificationField([
                'company_name'    => $request->name,
                'resource_name'   => $request->name,
                'resource_mobile' => $mobile
            ]));
        }
    }

    /**
     * @param $mobile
     * @return bool
     * @throws AlreadyExistProfile
     * @throws AlreadyReferred
     */
    private function validateStore($mobile)
    {

        $profile = Resource::query()->leftJoin('profiles', 'resources.profile_id', '=', 'profiles.id')->where('profiles.mobile', $mobile)->first();
        if (!empty($profile)) {
            throw new AlreadyExistProfile();
        }
        $request = PartnerReferral::query()->where('resource_mobile', $mobile)->first();
        if ($request)
            throw new AlreadyReferred();
        return true;
    }
}
