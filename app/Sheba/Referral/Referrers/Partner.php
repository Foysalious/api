<?php namespace Sheba\Referral\Referrers;

use App\Models\PartnerReferral;
use App\Models\Resource;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Sheba\ModificationFields;
use Sheba\Referral\Exceptions\AlreadyExistProfile;
use Sheba\Referral\Exceptions\AlreadyReferred;
use Sheba\Referral\Exceptions\InvalidFilter;
use Sheba\Referral\Exceptions\ReferenceNotFound;
use Sheba\Referral\HasReferrals;
use Sheba\Referral\Referrer;
use Sheba\Referral\ReferrerInterface;
use Sheba\Sms\Sms;

class Partner extends Referrer implements ReferrerInterface
{
    use ModificationFields;
    private $config;
    private $orderFilters;

    public function __construct(HasReferrals $referrer)
    {
        $this->referrer     = $referrer;
        $this->config       = config('partner.referral_steps');
        $this->orderFilters = [
            'created_at' => 'partner_referrals.created_at',
            'income'     => 'ref.referrer_income'
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
            (new Sms())->shoot($mobile, "");
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

    /**
     * @param $id
     * @return array
     * @throws ReferenceNotFound
     */
    function details($id)
    {
        $ref = $this->attachSelect($this->init())->where('partner_referrals.id', $id)->first();
        if (empty($ref))
            throw new ReferenceNotFound();
        $ref_data              = $this->generateDetails($ref);
        $last_use              = $ref->refer->usage()->get()->last();
        $ref_data['last_used'] = !empty($last_use) ? $last_use->created_at->format('Y-m-d H:s:i') : null;
        return $ref_data;
    }

    private function attachSelect($query)
    {
        return $query->select([
            'partner_referrals.resource_mobile',
            'partner_referrals.resource_name',
            'partner_referrals.partner_id',
            'partner_referrals.referred_partner_id',
            'ref.referrer_income',
            'ref.refer_level',
            'partner_referrals.id',
            'partner_referrals.created_at'
        ])->selectRaw('COUNT(DISTINCT(DATE(`partner_usages_history`.`created_at`))) as usages')->leftJoin('partners', 'partners.id', '=', 'partner_referrals.partner_id')->leftJoin('partners as ref', 'ref.id', '=', 'partner_referrals.referred_partner_id')->leftJoin('partner_usages_history', 'partner_referrals.referred_partner_id', '=', 'partner_usages_history.partner_id')->groupBy('partner_referrals.id');
    }

    private function generateDetails($refer)
    {
        $reference                   = [];
        $reference['id']             = $refer->id;
        $reference['name']           = $refer->refer ? $refer->refer->name : $refer->resource_name;
        $reference['contact_number'] = $refer->refer ? $refer->refer->getContactNumber() : $refer->resource_mobile;
        $reference['income']         = $refer->referrer_income ?: 0;
        $reference['milestone']      = $this->getMilestoneForPartner($refer);
        $reference['usage']          = $refer->usages;
        $reference['step']           = $refer->refer_level;
        $reference['step_bn']        = $reference['milestone']['current_step'];
        $reference['created_at']     = $refer->created_at->format('Y-m-d H:s:i');
        return $reference;
    }

    private function getMilestoneForPartner($partner_referral)
    {
        $config = $this->config;
        if ($partner_referral->refer) {
            $usage    = (int)$partner_referral->usages;
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

    function totalRefer()
    {
        $ref = $this->init()->selectRaw('count(*) as count')->first();
        return $ref ? $ref->count : 0;
    }

    function totalSuccessfulRefer()
    {
        $ref = $this->referrer->referrals()->selectRaw('count(*) as count')->where('status', 'successful')->first();
        return $ref ? $ref->count : 0;
    }

    public function home()
    {
        return [
            'income' => $this->totalIncome(),
            'link'=>config('sheba.front_url').'/rf/'.$this->referrer->refer_code
        ];
    }

    function totalIncome()
    {
        $ref=$this->attachSelect($this->init())->get()->sum('referrer_income');
        return round((double)$ref?:0, 2);
    }

    /**
     * @param Request $request
     * @return Collection
     * @throws InvalidFilter
     */
    function getReferrals(Request $request): Collection
    {
        $refers       = $this->attachSelect($this->init());
        $this->refers = $this->filterRefers($request, $refers)->get();
        return $this->formatRefers();
    }

    private function filterRefers(Request $request, $query)
    {
        if ($request->has('order_by')) {
            $orderBy = $request->order_by;
            $order   = $request->order ?: 'asc';
            if (!isset($this->orderFilters[$orderBy]))
                throw new InvalidFilter("$orderBy filter is invalid");
            $query = $query->orderBy($this->orderFilters[$orderBy], $order);
        }
        if ($request->has('step')) {
            $query = $query->where('ref.refer_level', (int)$request->step);
        }
        return $query;
    }

    private function formatRefers()
    {
        $all_referred = collect();
        foreach ($this->refers as $refer) {
            $all_referred->push($this->generateDetails($refer));
        }
        return $all_referred;
    }
}
