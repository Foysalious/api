<?php namespace Sheba\Referral\Referrers;

use App\Models\PartnerReferral;
use App\Models\Resource;
use App\Repositories\SmsHandler;
use Sheba\Sms\BusinessType;
use Sheba\Sms\FeatureType;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Sheba\FraudDetection\TransactionSources;
use Sheba\ModificationFields;
use Sheba\Referral\Exceptions\AlreadyExistProfile;
use Sheba\Referral\Exceptions\AlreadyReferred;
use Sheba\Referral\Exceptions\InvalidFilter;
use Sheba\Referral\Exceptions\ReferenceNotFound;
use Sheba\Referral\HasReferrals;
use Sheba\Referral\Referrer;
use Sheba\Referral\ReferrerInterface;
use Sheba\Sms\Sms;
use Sheba\Transactions\Types;
use Sheba\Transactions\Wallet\WalletTransactionHandler;

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
    private function sendSms($mobile)
    {
        $partner = $this->referrer->getContactPerson() ;
        (new SmsHandler('partner-referral-create'))
            ->setBusinessType(BusinessType::SMANAGER)
            ->setFeatureType(FeatureType::PARTNER_REFERRAL)
            ->send($mobile, [
                'partner' => $partner
            ]);
    }

    private function notify($ref)
    {
        notify()->department(7)->send([
            'title' => 'New SP Referral Arrived from ' . $this->referrer->getContactNumber(),
            'link' => config('sheba.admin_url') . '/partner-referrals/' . $ref->id,
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
        if (!empty($profile)) throw new AlreadyExistProfile();

        $request = PartnerReferral::query()->where('resource_mobile', $mobile)->first();
        if ($request) throw new AlreadyReferred();
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
        $ref_data['last_used'] = !empty($last_use) ? $last_use->created_at->format('Y-m-d H:i:s') : null;
        $ref_data['stepwise_income'] = collect(config('partner.referral_steps'))->where('visible', true)
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
        $reference['income']         = !empty($this->updatedRefer) ? ($this->updatedRefer->referrer_income ?: 0) : ($refer->referrer_income ?: 0);
        $reference['usage']          = $refer->usages;
        $reference['step']           = !empty($this->updatedRefer) ? $this->updatedRefer->refer_level : (int) $refer->refer_level;
        $reference['step_bn']        = $reference['milestone']['current_step'];
        $reference['created_at']     = $refer->created_at->format('Y-m-d H:i:s');
        $this->updatedRefer = null;
        return $reference;
    }

    private function getMilestoneForPartner($partner_referral)
    {


        $config =collect($this->config)->where('visible', true)->toArray();
        if ($partner_referral->refer) {
            $usage    = (int)$partner_referral->usages;
            $earnings = 0;
            foreach ($config as $key => $configuration) {
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
            $key=count($config)-1;
            return [
                'start'            =>  $config[$key]['duration'] ,
                'end'              => 0,
                'nid_verification' => $config[$key]['nid_verification'],
                'future_earning'   => 0,
                'current_step'     =>  $config[$key]['step'],
                'future_step'      => $config[$key]['step']
            ];
        }
        return [
            'start'            => 0,
            'end'              => $config[0]['duration'],
            'nid_verification' => $config[0]['nid_verification'],
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
        if ($request->has('q') && !empty($request->q)) {
            $query = $request->q;
            return $this->formatRefers()->filter(function ($item) use ($query) {
                return strpos( $item['name'],"$query")!==false || strpos( $item['contact_number'],"$query")!==false;
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
            if((int)$request->step == 0)
                $query = $query->where('ref.refer_level', (int)$request->step)->orWhere('ref.refer_level', null);
            else
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
