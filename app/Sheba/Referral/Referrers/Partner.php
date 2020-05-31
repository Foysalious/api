<?php namespace Sheba\Referral\Referrers;

use App\Models\PartnerReferral;
use App\Models\Resource;
use App\Repositories\SmsHandler;
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
    /**
     * @var \App\Models\Partner
     */
    private $updatedRefer;

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
     * @throws \Exception
     */
    function store(Request $request)
    {
        $mobile = formatMobile($request->mobile);
        if ($this->validateStore($mobile)) {
            $this->setModifier($this->referrer);
            $ref=$this->referrer->referrals()->create($this->withCreateModificationField([
                'company_name'    => $request->name,
                'resource_name'   => $request->name,
                'resource_mobile' => $mobile
            ]));
            $this->sendSms($mobile);
            $this->notify($ref);
        }
    }

    /**
     * @param $mobile
     * @throws \Exception
     */
    private function sendSms($mobile){
        $partner = $this->referrer->getContactPerson() ;
        (new SmsHandler('partner-referral-create'))->send($mobile, [
            'partner' => $partner
        ]);
    }
    private function notify($ref){
        notify()->department(7)->send([
            'title' => 'New SP Referral Arrived from ' . $this->referrer->getContactNumber(),
            'link' => env('SHEBA_BACKEND_URL') . '/partner-referrals/' . $ref->id,
            'type' => notificationType('Info'),
            'event_type' => "App\\Models\\" . class_basename($ref),
            'event_id' => $ref->id
        ]);
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
        $ref_data['stepwise_income'] = collect(config('partner.referral_steps'))
            ->map(function($item) {
                return $item['amount'];
            });
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
        $reference['milestone']      = $this->getMilestoneForPartner($refer);
        $reference['id']             = $refer->id;
        $reference['name']           = $refer->refer ? $refer->refer->name : $refer->resource_name;
        $reference['contact_number'] = $refer->refer ? $refer->refer->getContactNumber() : $refer->resource_mobile;
        $reference['income']         = !empty($this->updatedRefer) ? ($this->updatedRefer->referrer_income ?: 0) : ($refer->referrer_income ?: $this->config[0]['amount']) ;
        $reference['usage']          = $refer->usages;
        $reference['step']           = !empty($this->updatedRefer) ? $this->updatedRefer->refer_level : (int) $refer->refer_level;
        $reference['step_bn']        = $reference['milestone']['current_step'];
        $reference['created_at']     = $refer->created_at->format('Y-m-d H:s:i');
        $this->updatedRefer = null;
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
                {
                   if($partner_referral->refer->isNIDVerified() && ($partner_referral->refer->refer_level != $key+1))
                    {
                        $partner_referral->refer->refer_level = $key+1;
                        $partner_referral->refer->referrer_income = $earnings + $configuration['amount'];
                        $partner_referral->refer->update();
                        $partnerModel = 'App\Models\Partner';
                        $this->updatedRefer = $partnerModel::find($partner_referral->refer->id);

                    }
                    return [
                        'start'            => 0,
                        'end'              => 0,
                        'nid_verification' => true,
                        'future_earning'   => !$partner_referral->refer->isNIDVerified() ? $earnings + $configuration['amount'] : null,
                        'current_step'     => $partner_referral->refer->isNIDVerified() ? $config[$key]['step'] : $config[$key-1]['step'],
                        'future_step'      => !$partner_referral->refer->isNIDVerified()  ?   $config[$key]['step'] : (isset($config[$key+1]) ? $config[$key+1] : null )
                    ];

                }

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
            'nid_verification' => false,
            'future_earning'   => $config[0]['amount'],
            'current_step'     => 'পেন্ডিং',
            'future_step'      => $config[0]['step']
        ];
    }

  /*  private function getMilestoneForPartner($partner_referral)
    {
        $config = $this->config;
        if ($partner_referral->refer) {
            $usage    = (int)$partner_referral->usages;
            $earnings = 0;
            foreach ($config as $key => $configuration) {
                if ($configuration['nid_verification'] && $partner_referral->refer->isNIDVerified()){
                    $this->resolveLevelAndIncome($partner_referral,$key+1,$earnings + $configuration['amount']);
                    return [
                        'start'            => 0,
                        'end'              => 0,
                        'nid_verification' => true,
                        'future_earning'   => null,
                        'current_step'     => $config[$key]['step'],
                        'future_step'      => isset($config[$key + 1]) ? $config[$key + 1]['step'] : null
                    ];
                }

                if ($configuration['nid_verification'] && !$partner_referral->refer->isNIDVerified())
                    return [
                        'start'            => 0,
                        'end'              => 0,
                        'nid_verification' => true,
                        'future_earning'   => $earnings + $configuration['amount'],
                        'current_step'     => $config[$key-1]['step'],
                        'future_step'      => isset($config[$key]) ? $config[$key]['step'] : null
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
            'nid_verification' => false,
            'future_earning'   => $config[0]['amount'],
            'current_step'     => 'পেন্ডিং',
            'future_step'      => $config[0]['step']
        ];
    }

    private function resolveLevelAndIncome($partner_referral,$level,$income)
    {
        if($partner_referral->refer->refer_level != $level  || ((double)$partner_referral->refer->referrer_income != (double)$income))
        {
            $partner_referral->refer->refer_level = $level;
            $partner_referral->refer->referrer_income = $income;
            $partner_referral->refer->update();
        }
    }*/
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
        if ($request->has('q') && !empty($request->q)) {
            $query = $request->q;
            return $this->formatRefers()->filter(function ($item) use ($query) {
                return preg_match("/$query/i", $item['name']) || preg_match("/$query/", $item['contact_number']);
            })->values();
        }
        if ($request->has('limit') && !empty($request->limit)) {
            list($offset, $limit) = calculatePagination($request);
            return $this->formatRefers()->slice($offset)->take($limit)->values();
        }

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
            if((int)$request->step == 0) $query = $query->orWhere('ref.refer_level', null);
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
