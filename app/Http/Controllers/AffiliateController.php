<?php namespace App\Http\Controllers;

use App\Models\Affiliate;
use App\Models\AffiliateTransaction;
use App\Models\Affiliation;
use App\Models\Job;
use App\Models\Partner;
use App\Models\PartnerAffiliation;
use App\Models\PartnerOrder;
use App\Models\Profile;
use App\Models\ProfileBankInformation;
use App\Models\ProfileMobileBankInformation;
use App\Models\Resource;
use App\Models\TopUpOrder;
use Sheba\Dal\Service\Service;
use App\Repositories\AffiliateRepository;
use App\Repositories\FileRepository;
use App\Repositories\LocationRepository;
use App\Sheba\BankingInfo\GeneralBanking;
use App\Sheba\BankingInfo\MobileBanking;
use App\Sheba\Bondhu\AffiliateHistory;
use App\Sheba\Bondhu\AffiliateStatus;
use App\Sheba\Bondhu\TopUpEarning;
use App\Transformers\Affiliate\BankDetailTransformer;
use App\Transformers\Affiliate\MobileBankDetailTransformer;
use App\Transformers\Affiliate\ProfileDetailPersonalInfoTransformer;
use App\Transformers\Affiliate\ProfileDetailTransformer;
use App\Transformers\CustomSerializer;
use Carbon\Carbon;
use DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use League\Fractal\Manager;
use League\Fractal\Resource\Item;
use Sheba\Bondhu\Statuses;
use Sheba\Dal\TopupOrder\TopUpOrderRepository;
use Sheba\FraudDetection\TransactionSources;
use Sheba\Logs\Customer\JobLogs;
use Sheba\ModificationFields;
use Sheba\Reports\ExcelHandler;
use Sheba\Repositories\Interfaces\ProfileBankingRepositoryInterface;
use Sheba\Repositories\Interfaces\ProfileMobileBankingRepositoryInterface;
use Sheba\Repositories\Interfaces\ProfileRepositoryInterface;
use Sheba\TopUp\History\RequestBuilder;
use Sheba\Transactions\InvalidTransaction;
use Sheba\Transactions\Types;
use Sheba\Transactions\Wallet\WalletTransactionHandler;
use Throwable;
use Validator;
use Sheba\Dal\TopUpVendorOTF\Contract as TopUpVendorOTFRepo;

class AffiliateController extends Controller
{
    use ModificationFields;

    private $fileRepository;
    private $locationRepository;
    private $affiliateRepository;
    private $nidOcrRepo;

    public function __construct(FileRepository $file_repo, LocationRepository $location_repo, AffiliateRepository $affiliate_repo)
    {
        $this->fileRepository = $file_repo;
        $this->locationRepository = $location_repo;
        $this->affiliateRepository = $affiliate_repo;
    }

    public function edit($affiliate, Request $request)
    {
        $except_fields = [];
        if ($request->has('bkash_no')) {
            $mobile = formatMobile(ltrim($request->bkash_no));
            $request->merge(['bkash_no' => $mobile]);
        } else {
            $except_fields = ['bkash_no'];
        }
        $validation_fields = count($except_fields) > 0 ? $request->except($except_fields) : $request->all();
        if ($msg = $this->_validateEdit($validation_fields)) {
            return response()->json(['code' => 500, 'msg' => $msg]);
        }
        $affiliate = Affiliate::find($affiliate);
        if ($request->has('name') || $request->has('address')) {
            /** @var Profile $profile */
            $profile = $affiliate->profile;
            if ($request->has('name')) $profile->name = $request->name;
            if ($request->has('address')) $profile->address = $request->address;
            $profile->update();
        }
        if ($request->has('bkash_no')) {
            $banking_info = $affiliate->banking_info;
            $banking_info->bKash = $mobile;
            $affiliate->banking_info = json_encode($banking_info);
        }
        if ($request->has('geolocation')) {
            $affiliate->geolocation = $request->geolocation;
        }

        return $affiliate->update() ? response()->json(['code' => 200]) : response()->json(['code' => 404]);
    }

    private function _validateEdit($request)
    {
        $validator = Validator::make($request, [
            'bkash_no' => 'sometimes|required|string|mobile:bd',
        ], ['mobile' => 'Invalid bKash number!']);
        return $validator->fails() ? $validator->errors()->all()[0] : false;
    }

    public function getStatus($affiliate, Request $request)
    {
        $affiliate = Affiliate::where('id', $affiliate)
            ->select('verification_status', 'is_suspended', 'ambassador_code', 'is_ambassador', 'is_moderator')
            ->first();
        return $affiliate != null ? response()->json(['code' => 200, 'affiliate' => $affiliate]) : response()->json(['code' => 404, 'msg' => 'Not found!']);
    }

    public function getDashboardInfo($affiliate, Request $request)
    {
        /** @var Affiliate $affiliate */
        $affiliate = Affiliate::find($affiliate);
        $info = [
            'wallet' => (double)$affiliate->wallet,
            'total_income' => (double)$affiliate->getIncome(),
            'total_service_referred' => $affiliate->affiliations->count(),
            'total_sp_referred' => $affiliate->partnerAffiliations->count(),
            'last_updated' => Carbon::parse($affiliate->updated_at)->format('dS F,g:i A'),
            'robi_topup_wallet' => (double)$affiliate->robi_topup_wallet,
            "is_robi_retailer" => $affiliate->retailers->where('strategic_partner_id', 2)->count() ? 1 : 0,
            'total_notifications' => $affiliate->notifications()->count(),
            'total_unseen_notifications' => $affiliate->notifications()->where('is_seen', 0)->count(),

        ];
        $affiliate->update(["last_login" => Carbon::now()]);
        return api_response($request, $info, 200, ['info' => $info]);
    }

    public function updateProfilePic(Request $request)
    {
        if ($msg = $this->_validateImage($request)) {
            return response()->json(['code' => 500, 'msg' => $msg]);
        }
        $photo = $request->file('photo');
        $profile = ($request->affiliate)->profile;
        if (basename($profile->pro_pic) != 'default.jpg') {
            $filename = substr($profile->pro_pic, strlen(env('S3_URL')));
            $this->fileRepository->deleteFileFromCDN($filename);
        }
        $filename = $profile->id . '_profile_image_' . Carbon::now()->timestamp . '.' . $photo->extension();
        $profile->pro_pic = $this->fileRepository->uploadToCDN($filename, $request->file('photo'), 'images/profiles/');
        return $profile->update() ? response()->json(['code' => 200, 'picture' => $profile->pro_pic]) : response()->json(['code' => 404]);
    }

    private function _validateImage($request)
    {
        $validator = Validator::make($request->all(), [
            'photo' => 'required|mimes:jpeg,png|max:500'
        ]);
        return $validator->fails() ? $validator->errors()->all()[0] : false;
    }

    public function getWallet($affiliate, Request $request)
    {
        $affiliate = Affiliate::find($affiliate);
        return $affiliate != null ? response()->json(['code' => 200, 'wallet' => $affiliate->wallet]) : response()->json(['code' => 404, 'msg' => 'Not found!']);
    }

    public function leadInfo($affiliate, AffiliateStatus $status, Request $request)
    {
        $rules = [
            'filter_type' => 'required|string',
            'from' => 'required_if:filter_type,date_range',
            'to' => 'required_if:filter_type,date_range',
            'sp_type' => 'required|in:affiliates,partner_affiliates,lite',
            'agent_id' => 'numeric'
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $error = $validator->errors()->all()[0];
            return api_response($request, $error, 400, ['msg' => $error]);
        }
        if ((int)$request->agent_data)
            $status = $status->setType($request->sp_type)->getFormattedDate($request)->getAgentsData($affiliate);
        else
            $status = $status->setType($request->sp_type)->getFormattedDate($request)->getIndividualData($request->agent_id);

        return response()->json(['code' => 200, 'data' => $status]);
    }

    public function getAmbassador($affiliate, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string'
        ]);
        if ($validator->fails()) {
            $error = $validator->errors()->all()[0];
            return api_response($request, $error, 400, ['msg' => $error]);
        }
        $ambassador = Affiliate::with(['profile' => function ($q) {
            $q->select('id', 'name', DB::raw('pro_pic as picture'));
        }])->where([
            ['ambassador_code', strtoupper(trim($request->code))],
            ['is_ambassador', 1]
        ])->first();
        if ($ambassador) {
            return api_response($request, $ambassador->profile, 200, ['info' => $ambassador->profile]);
        } else {
            return api_response($request, null, 404);
        }
    }

    /**
     * @param $affiliate
     * @param Request $request
     * @return JsonResponse
     */
    public function joinClan($affiliate, Request $request)
    {
        $this->validate($request, ['code' => 'required|string']);
        $affiliate = $request->affiliate;

        if ($affiliate->ambassador_id || $affiliate->previous_ambassador_id)
            return api_response($request, null, 403);

        $agents_ids = $affiliate->agents->pluck('id')->toArray();
        array_push($agents_ids, $affiliate->id);
        $ambassador = Affiliate::where('ambassador_code', strtoupper(trim($request->code)))
            ->where('is_ambassador', 1)
            ->whereNotIn('id', $agents_ids)
            ->first();

        if (!$ambassador) return api_response($request, null, 404);

        $affiliate->ambassador_id = $ambassador->id;
        $affiliate->under_ambassador_since = Carbon::now();
        $affiliate->update();

        return api_response($request, $ambassador, 200);
    }

    public function getAgents($affiliate, Request $request)
    {
        $affiliate = $request->affiliate;
        if ($affiliate->is_ambassador == 0) {
            return api_response($request, null, 403);
        }
        $q = $request->get('query');
        $range = $request->get('range');
        $sort_order = $request->get('sort_order');

        list($offset, $limit) = calculatePagination($request);

        $agents = collect(DB::select('SELECT 
affiliates.id,
affiliates.profile_id,
  (Sum(affiliate_transactions.amount))  AS total_gifted_amount, 
 Count(DISTINCT( affiliate_transactions.id )) AS total_gifted_number,
affiliates.profile_id,
affiliates.ambassador_id,
affiliates.under_ambassador_since,
profiles.name,
profiles.pro_pic AS picture,
profiles.mobile,
affiliates.created_at
FROM
affiliate_transactions
    LEFT JOIN
`affiliates` ON `affiliate_transactions`.`affiliate_id` = `affiliates`.`id`
    LEFT JOIN
profiles ON affiliates.profile_id = profiles.id
WHERE
     affiliate_id IN (SELECT 
        id
    FROM
        affiliates
    WHERE
        ambassador_id = ?)
GROUP BY affiliate_transactions.affiliate_id', [$affiliate->id]));

        $agents->map(function ($agent) {
            $agent->total_gifted_amount = (double)$agent->total_gifted_amount;
        });

        $agents = $this->filterAgents($q, $agents);

        $agents = $this->sortAgents($sort_order, $agents);

        $agents = $agents->splice($offset, $limit)->toArray();
        if (count($agents) > 0) {
            $response = ['agents' => $agents];
            if ($range) {
                $r_d = getRangeFormat($request);
                $response['range'] = ['to' => $r_d[0], 'from' => $r_d[1]];
            }
            return api_response($request, $agents, 200, $response);
        }
        return api_response($request, null, 404);
    }

    private function filterAgents($q, $agents)
    {
        if (isset($q) & !empty($q)) {
            return $agents->filter(function ($data) use ($q) {
                return str_contains($data['name'], $q);
            });
        }
        return $agents;
    }

    private function sortAgents($sort_order, $agents)
    {
        if (isset($sort_order)) {
            return ($sort_order == 'asc') ? $agents->sortBy('total_gifted_amount') : $agents->sortByDesc('total_gifted_amount');
        }
        return $agents;
    }

    public function getGodFather($affiliate, Request $request)
    {
        $affiliate = $request->affiliate;
        if ($affiliate->ambassador_id == null) {
            return api_response($request, null, 404);
        } else {
            $profile = collect($affiliate->ambassador->profile)->only(['name', 'pro_pic', 'mobile'])->all();
            $profile['picture'] = $profile['pro_pic'];
            array_forget($profile, 'pro_pic');
            return api_response($request, $profile, 200, ['info' => $profile]);
        }
    }

    public function getLeaderboard($affiliate, Request $request)
    {
        list($offset, $limit) = calculatePagination($request);
        $transactions = AffiliateTransaction::where('affiliation_type', '<>', null)
            ->with(['affiliate' => function ($q) {
                $q->with(['profile' => function ($q) {
                    $q->select('id', 'name', 'pro_pic');
                }, 'affiliations' => function ($q) {
                    $q->selectRaw('count(*) as total_reference, affiliate_id')->groupBy('affiliate_id');
                }, 'partnerAffiliations' => function ($q) {
                    $q->selectRaw('count(*) as total_partner_reference, affiliate_id')->groupBy('affiliate_id');
                }]);
            }])
            ->selectRaw('sum(amount) as earning_amount, affiliate_id')
            ->where('type', 'Credit')->groupBy('affiliate_id')->orderBy('earning_amount', 'desc')->skip($offset)->take($limit)->get();
        $final = [];
        foreach ($transactions as $transaction) {
            $info['id'] = $transaction->affiliate->id;
            $info['earning_amount'] = (double)$transaction->earning_amount;
            $total_affiliation = $transaction->affiliate->affiliations->first() ? $transaction->affiliate->affiliations->first()->total_reference : 0;
            $total_partner_affiliation = $transaction->affiliate->partnerAffiliations->first() ? $transaction->affiliate->partnerAffiliations->first()->total_partner_reference : 0;
            $info['total_reference'] = $total_affiliation + $total_partner_affiliation;
            $info['name'] = $transaction->affiliate->profile->name;
            $info['picture'] = $transaction->affiliate->profile->pro_pic;
            array_push($final, $info);
        }
        return count($final) != 0 ? api_response($request, $final, 200, ['affiliates' => $final]) : api_response($request, null, 404);
    }

    public function getAmbassadorSummary($affiliate, Request $request, TopUpEarning $top_up_earning)
    {
        $affiliate = $request->affiliate;
        if ($affiliate->is_ambassador == 0) {
            return api_response($request, null, 403);
        }

        $partner_affiliation_total_amount = DB::select('SELECT SUM(affiliate_transactions.amount) as total_amount FROM affiliate_transactions 
                LEFT JOIN `partner_affiliations` ON `affiliate_transactions`.`affiliation_id` = `partner_affiliations`.`id` 
                LEFT JOIN `affiliates` ON `partner_affiliations`.`affiliate_id` = `affiliates`.`id` 
                WHERE `affiliate_transactions`.`affiliation_type` = \'App\\\Models\\\PartnerAffiliation\'
                AND affiliate_transactions.affiliate_id = ? AND is_gifted = 1
                AND affiliates.under_ambassador_since < affiliate_transactions.created_at', [$affiliate->id]
        );
        $partner_affiliation_amount = $partner_affiliation_total_amount[0]->total_amount ?: 0;

        $lite_affiliation_total_amount = DB::select('SELECT SUM(affiliate_transactions.amount) as total_amount FROM affiliate_transactions 
                LEFT JOIN `affiliates` ON `affiliate_transactions`.`affiliate_id` = `affiliates`.`id` 
                WHERE `affiliate_transactions`.`affiliation_type` = \'App\\\Models\\\Partner\'
                AND affiliate_transactions.affiliate_id = ? AND is_gifted = 1 group by affiliate_transactions.affiliate_id
                ', [$affiliate->id]
        );

        $lite_total_amount = count($lite_affiliation_total_amount) > 0 ? ($lite_affiliation_total_amount[0]->total_amount ?: 0) : 0;
        $request->filter_type = 'lifetime';
        $topup_earning = $top_up_earning->setType('affiliate')->getFormattedDate($request)->getAgentsData($affiliate)['earning_amount'];
        $total_amount = $partner_affiliation_amount + $lite_total_amount + $topup_earning;

        $partner_affiliation_count = PartnerAffiliation::spCount($affiliate->id)->count();
        $lite_affiliation_count_query = DB::select('select count(*) as count from partners where affiliate_id in (select id from affiliates where ambassador_id = ?)', [$affiliate->id]);
        $lite_affiliation_count = $lite_affiliation_count_query[0]->count ?: 0;

        $info = collect();
        $info->put('agent_count', $affiliate->agents->count());
        $info->put('earning_amount', $affiliate->agents->sum('total_gifted_amount') + (double)$total_amount);
        $info->put('total_refer', Affiliation::totalRefer($affiliate->id)->count());
        $info->put('sp_count', $partner_affiliation_count + $lite_affiliation_count);
        return api_response($request, $info, 200, ['info' => $info->all()]);
    }

    public function lifeTimeGift($affiliate, $agent_id, Request $request)
    {
        $affiliate = $request->affiliate;
        if ($affiliate->is_ambassador == 0) {
            return api_response($request, null, 403);
        }
        $info = collect();
        $agent = $request->affiliate->agents()->where('id', $agent_id)->first();
        $sp = Affiliate::agentsWithFilter($request, 'partner_affiliations')->get()->filter(function ($d) use ($agent_id) {
            return $d['id'] == $agent_id;
        })->first();

        $lite_refers = collect(DB::select('SELECT 
affiliates.id,
affiliates.profile_id,
  (Sum(affiliate_transactions.amount))  AS total_gifted_amount, 
 Count(DISTINCT( affiliate_transactions.id )) AS total_gifted_number,
affiliates.profile_id,
affiliates.ambassador_id,
affiliates.under_ambassador_since,
profiles.name,
profiles.pro_pic AS picture,
profiles.mobile,
affiliates.created_at
FROM
affiliate_transactions
    LEFT JOIN
`affiliates` ON `affiliate_transactions`.`affiliate_id` = `affiliates`.`id`
    LEFT JOIN
profiles ON affiliates.profile_id = profiles.id
WHERE
     affiliate_id IN (SELECT 
        id
    FROM
        affiliates
    WHERE
        ambassador_id = ? AND affiliates.id = ?)
GROUP BY affiliate_transactions.affiliate_id', [$affiliate->id, $agent_id]));

        $lite_refers->map(function ($agent) {
            $agent->total_gifted_amount = (double)$agent->total_gifted_amount;
        });

        $gift_amount = $agent ? $agent->total_gifted_amount : 0;
        $gift_amount += $sp ? $sp->total_gifted_amount : 0;
        $gift_amount += count($lite_refers) > 0 ? $lite_refers[0]->total_gifted_amount : 0;
        $info->put('life_time_gift', $gift_amount);
        return api_response($request, $info, 200, $info->all());
    }

    public function getTransactions($affiliate, Request $request)
    {
        list($offset, $limit) = calculatePagination($request);
        $affiliate = $request->affiliate;
        $credit_affiliate = clone $affiliate;
        $debit_affiliate = clone $affiliate;

        $affiliate_credit_transactions = $credit_affiliate->load([
            'transactions' => function ($q) use ($offset, $limit) {
                $q->where('type', 'Credit')
                    ->select('id', 'affiliate_id', 'affiliation_id', 'type', 'log', 'amount', DB::raw('DATE_FORMAT(created_at, "%M %d, %Y at %h:%i %p") as time'))
                    ->orderBy('id', 'desc')
                    ->skip($offset)
                    ->take($limit);
            },
        ]);
        $affiliate_debit_transactions = $debit_affiliate->load([
            'transactions' => function ($q) use ($offset, $limit) {
                $q->where('type', 'Debit')
                    ->select('id', 'affiliate_id', 'affiliation_id', 'type', 'log', 'amount', DB::raw('DATE_FORMAT(created_at, "%M %d, %Y at %h:%i %p") as time'))
                    ->orderBy('id', 'desc')
                    ->skip($offset)
                    ->take($limit);
            }
        ]);

        if ($affiliate->transactions()->count()) {
            $credit = $affiliate_credit_transactions->transactions->values()->all();
            $debit = $affiliate_debit_transactions->transactions->values()->all();

            return api_response($request, null, 200, ['credit' => $credit, 'debit' => $debit]);
        } else {
            return api_response($request, null, 404);
        }
    }

    public function getNotifications($affiliate, Request $request)
    {
        list($offset, $limit) = calculatePagination($request);
        $notifications = $request->affiliate->notifications()->select('id', 'title', 'event_type', 'event_id', 'type', 'is_seen', 'created_at')->orderBy('id', 'desc')->skip($offset)->limit($limit)->get();
        if (count($notifications) == 0) return api_response($request, null, 404);
        $notifications = $notifications->map(function ($notification) {
            $notification->event_type = str_replace('App\Models\\', "", $notification->event_type);
            array_add($notification, 'timestamp', $notification->created_at->timestamp);
            return $notification;
        });
        return api_response($request, $notifications, 200, ['notifications' => $notifications]);
    }

    public function getNotification($affiliate, $notification, Request $request)
    {
        $notifications = $request->affiliate->notifications()->select('id', 'title', 'description', 'event_type', 'event_id', 'type', 'is_seen', 'created_at')->where('id', $notification)->get();
        if (count($notifications) == 0) return api_response($request, null, 404);
        $notifications = $notifications->map(function ($notification) {
            $notification->event_type = str_replace('App\Models\\', "", $notification->event_type);
            array_add($notification, 'timestamp', $notification->created_at->timestamp);
            return $notification;
        });
        return api_response($request, $notifications, 200, ['notification' => $notifications]);
    }

    /**
     * @param $affiliate
     * @param Request $request
     * @return JsonResponse
     */
    public function rechargeWallet($affiliate, Request $request)
    {
        try {
            $this->validate($request, [
                'transaction_id' => 'required|string',
                'type' => 'required|in:bkash',
            ]);

            return api_response($request, null, 403, ['message' => 'Recharge system turned off']);

            /*$affiliate   = $request->affiliate;
            $transaction = (new Registrar())->register($affiliate, $request->type, $request->transaction_id);

            $this->setModifier($affiliate);
            $this->recharge($affiliate, $transaction);
            return api_response($request, null, 200, ['message' => "Moneybag refilled."]);*/
        } catch (InvalidTransaction $e) {
            logError($e);
        }
    }

    private function recharge(Affiliate $affiliate, $transaction)
    {
        $data = $this->makeRechargeData($transaction);
        $amount = $transaction['amount'];
        (new WalletTransactionHandler())->setModel($affiliate)->setSource(TransactionSources::BKASH)->setTransactionDetails($data['transaction_details'])->setType(Types::credit())->setAmount($amount)->setLog($data['log'])->dispatch();
    }

    private function makeRechargeData($transaction)
    {
        return [
            'amount' => $transaction['amount'],
            'transaction_details' =>
                [
                    'name' => 'Bkash',
                    'details' => [
                        'transaction_id' => $transaction['transaction_id'],
                        'gateway' => 'bkash',
                        'details' => $transaction['details']
                    ]
                ]
            ,
            'type' => 'Credit',
            'log' => 'Moneybag Refilled'
        ];
    }

    public function getServicesInfo($affiliate, Request $request)
    {
        $services = Service::select('id', 'category_id', 'name', 'description', 'bn_name', 'app_thumb', 'banner', 'min_quantity', 'unit')
            ->publishedForBondhu()->orderByRaw('order_for_bondhu IS NULL, order_for_bondhu')
            ->get()->map(function ($service) {
                return [
                    'id' => $service->id,
                    'name' => $service->name,
                    'bangla_name' => empty($service->bn_name) ? null : $service->bn_name,
                    'image' => $service->app_thumb,
                    'min_quantity' => $service->min_quantity,
                    'unit' => $service->unit,
                    'category_id' => $service->category_id,
                    'banner' => $service->banner,
                    'description' => $service->description
                ];
            });
        return api_response($request, $services, 200, ['services' => $services]);
    }

    public function history($affiliate, AffiliateHistory $history, Request $request)
    {
        $rules = [
            'filter_type' => 'required|string',
            'from' => 'required_if:filter_type,date_range',
            'to' => 'required_if:filter_type,date_range',
            'sp_type' => 'required|in:affiliates,partner_affiliates',
            'agent_id' => 'required'
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $error = $validator->errors()->all()[0];
            return api_response($request, $error, 400, ['msg' => $error]);
        }
        list($offset, $limit) = calculatePagination($request);
        $historyData = $history->setType($request->sp_type)->getFormattedDate($request)->generateData($affiliate, $request->agent_id)->skip($offset)->take($limit)->get();
        return response()->json(['code' => 200, 'data' => $historyData]);
    }

    public function topUpEarning($affiliate, TopUpEarning $top_up_earning, Request $request)
    {
        $rules = [
            'filter_type' => 'required|string',
            'from' => 'required_if:filter_type,date_range',
            'to' => 'required_if:filter_type,date_range',
            'sp_type' => 'required|in:affiliates,partner_affiliates',
            'agent_id' => 'required'
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $error = $validator->errors()->all()[0];
            return api_response($request, $error, 400, ['msg' => $error]);
        }
        if ((int)$request->agent_data)
            $earning = $top_up_earning->setType($request->sp_type)->getFormattedDate($request)->getAgentsData($affiliate);
        else
            $earning = $top_up_earning->setType($request->sp_type)->getFormattedDate($request)->getIndividualData($request->agent_id);
        return response()->json(['code' => 200, 'data' => $earning]);
    }

    /**
     * @param $affiliate
     * @param Request $request
     * @param RequestBuilder $request_builder
     * @param TopUpOrderRepository $top_up_order_repo
     * @param TopUpVendorOTFRepo $topup_vendor_otf
     * @return JsonResponse
     */
    public function topUpHistory($affiliate, Request $request, RequestBuilder $request_builder, TopUpOrderRepository $top_up_order_repo, TopUpVendorOTFRepo $topup_vendor_otf): JsonResponse
    {
        $rules = ['from' => 'date_format:Y-m-d', 'to' => 'date_format:Y-m-d|required_with:from'];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $error = $validator->errors()->all()[0];
            return api_response($request, $error, 400, ['msg' => $error]);
        }

        list($offset, $limit) = calculatePagination($request);
        $affiliate = Affiliate::find($affiliate);

        $is_excel_report = $request->has('content_type') && $request->content_type == 'excel';
        if ($is_excel_report) { $offset = 0; $limit = 100000; }

        $request_builder->setOffset($offset)->setLimit($limit)->setAgent($affiliate);
        if ($request->has('from') && $request->from !== "null") {
            $from_date = Carbon::parse($request->from);
            $to_date = Carbon::parse($request->to)->endOfDay();
            $request_builder->setFromDate($from_date)->setToDate($to_date);
        }
        if ($request->has('vendor_id') && $request->vendor_id !== "null") $request_builder->setVendorId($request->vendor_id);
        if ($request->has('status') && $request->status !== "null") $request_builder->setStatus($request->status);
        if ($request->has('q') && $request->q !== "null") $request_builder->setSearchQuery($request->q);
        if ($request->has('from_robi_topup_wallet') && $request->from_robi_topup_wallet == 1) $request_builder->setIsRobiTopupWallet($request->from_robi_topup_wallet);

        $total_topups = $top_up_order_repo->getTotalCountByFilter($request_builder);
        $topups = $top_up_order_repo->getByFilter($request_builder);

        $topup_otfs = $topup_vendor_otf->builder()->get()->pluckMultiple(['name_en', 'name_bn'], 'id')->toArray();
        $topup_data = [];
        foreach ($topups as $topup) {
            /** @var TopUpOrder $topup */
            array_push($topup_data, [
                'payee_mobile' => $topup->payee_mobile,
                'payee_name' => $topup->payee_name ?: 'N/A',
                'amount' => $topup->amount,
                'operator' => $topup->vendor->name,
                'status' => $topup->getStatusForAgent(),
                'otf_name_en' => isset($topup_otfs[$topup->otf_id]) ? $topup_otfs[$topup->otf_id]['name_en'] : "",
                'otf_name_bn' => isset($topup_otfs[$topup->otf_id]) ? $topup_otfs[$topup->otf_id]['name_bn'] : "",
                'created_at' => $topup->created_at->format('jS M, Y h:i A'),
                'created_at_raw' => $topup->created_at->format('Y-m-d H:i:s')
            ]);
        }

        if ($is_excel_report) {
            $excel = app(ExcelHandler::class);
            $excel->setName('Topup History');
            $excel->setViewFile('topup_history');
            $excel->pushData('topup_data', $topup_data);
            $excel->download();
        }

        return response()->json(['code' => 200, 'data' => $topup_data, 'total_topups' => $total_topups, 'offset' => $offset]);
    }

    public function getCustomerInfo(Request $request)
    {
        $this->validate($request, [
            'mobile' => 'required|mobile:bd'
        ]);
        $profile = Profile::where('mobile', '+88' . $request->mobile)->first();
        if (!is_null($profile)) {
            $customer_name = $profile->name;

            return api_response($request, $customer_name, 200, ['name' => $customer_name]);
        }
        return api_response($request, [], 404, ['message' => 'Customer not found.']);
    }

    public function getPartnerInfo(Request $request)
    {
        $this->validate($request, [
            'partner_id' => 'required|numeric'
        ]);
        $partner = Partner::find($request->partner_id);
        if (!is_null($partner)) {
            $customer_name = $partner->name;

            return api_response($request, $customer_name, 200, ['name' => $customer_name]);
        }
        return api_response($request, [], 404, ['message' => 'Customer not found.']);
    }

    /**
     * @param $affiliate
     * @param Request $request
     * @return JsonResponse
     */
    public function profileDetails($affiliate, Request $request)
    {
        $affiliate = $request->affiliate;
        $is_verified = $affiliate->verification_status;

        $member_since = date_format($affiliate->created_at, 'Y-m-d');
        $manager = new Manager();
        $manager->setSerializer(new CustomSerializer());
        $resource = new Item($affiliate->profile, new ProfileDetailTransformer());
        $details = $manager->createData($resource)->toArray()['data'];
        $details['is_verified'] = $is_verified;
        $details['member_since'] = $member_since;
        return api_response($request, null, 200, ['data' => $details]);
    }

    /**
     * @param $affiliate
     * @param Request $request
     * @param ProfileRepositoryInterface $profile_repo
     * @return JsonResponse
     */
    public function updatePersonalInformation($affiliate, Request $request, ProfileRepositoryInterface $profile_repo)
    {
        $affiliate = $request->affiliate;
        list($is_access_denied, $msg) = $this->checkUpdateParamsOnVerifiedStatus($affiliate, $request);
        if ($is_access_denied) return api_response($request, null, 403, ['msg' => $msg]);

        $this->validate($request, [
            'dob' => 'date',
        ]);

        $this->setModifier($affiliate);

        $updatable_data = [];
        if ($request->name != null) $updatable_data['name'] = $request->name;
        if ($request->bn_name != null) $updatable_data['bn_name'] = $request->bn_name;
        if ($request->dob != null) $updatable_data['dob'] = $request->dob;
        if ($request->nid_no != null) $updatable_data['nid_no'] = $request->nid_no;
        if ($request->gender != null) $updatable_data['gender'] = $request->gender;

        $profile_repo->update($affiliate->profile, $updatable_data);

        $manager = new Manager();
        $manager->setSerializer(new CustomSerializer());
        $resource = new Item($affiliate->profile, new ProfileDetailPersonalInfoTransformer());
        $details = $manager->createData($resource)->toArray()['data'];
        return api_response($request, null, 200, ['data' => $details]);
    }

    /**
     * @param $affiliate
     * @param Request $request
     * @return array
     */
    public function checkUpdateParamsOnVerifiedStatus($affiliate, Request $request)
    {
        $restricted_keys = ['name', 'bn_name', 'dob', 'nid_no'];
        $msg = null;
        $access_denied = $affiliate->verification_status == Statuses::VERIFIED && !empty(array_intersect($restricted_keys, array_keys($request->all())));
        if ($access_denied) {
            $denied_field = [];
            if ($request->has('name')) array_push($denied_field, 'Name');
            if ($request->has('bn_name')) array_push($denied_field, 'Bangla Name');
            if ($request->has('dob')) array_push($denied_field, 'Date Of Birth');
            if ($request->has('nid_no')) array_push($denied_field, 'nid no');
            $msg = implode(', ', $denied_field) . ' field not changeable on verified status';
        }

        return [$access_denied, $msg];
    }

    public function storeBankInformation($affiliate, Request $request, ProfileBankingRepositoryInterface $profile_bank_repo)
    {
        $this->validate($request, [
            'bank_name' => 'required',
            'account_no' => 'required',
            'branch_name' => 'required',
        ]);
        $affiliate = $request->affiliate;
        $this->setModifier($affiliate);
        $data = $request->except('affiliate', 'remember_token');
        $data['profile_id'] = $request->affiliate->profile_id;
        $bank_details = $profile_bank_repo->create($data);
        $manager = new Manager();
        $manager->setSerializer(new CustomSerializer());
        $resource = new Item($bank_details, new BankDetailTransformer());

        $details = $manager->createData($resource)->toArray()['data'];

        return api_response($request, null, 200, ['data' => $details]);
    }

    public function storeMobileBankInformation($affiliate, Request $request, ProfileMobileBankingRepositoryInterface $profile_mobile_bank_repo)
    {
        $this->validate($request, [
            'bank_name' => 'required',
            'account_no' => 'required'
        ]);
        $affiliate = $request->affiliate;
        $this->setModifier($affiliate);
        $data = $request->except('affiliate', 'remember_token');
        $data['profile_id'] = $request->affiliate->profile_id;
        $bank_details = $profile_mobile_bank_repo->create($data);

        $manager = new Manager();
        $manager->setSerializer(new CustomSerializer());
        $resource = new Item($bank_details, new MobileBankDetailTransformer());

        $details = $manager->createData($resource)->toArray()['data'];

        return api_response($request, null, 200, ['data' => $details]);
    }

    public function updateBankInformation($affiliate, ProfileBankInformation $profile_bank_information, Request $request, ProfileBankingRepositoryInterface $profile_bank_repo)
    {
        $affiliate = $request->affiliate;
        $this->setModifier($affiliate);
        $data = $request->except('affiliate', 'remember_token');
        $bank_details = $profile_bank_repo->update($profile_bank_information, $data);

        $manager = new Manager();
        $manager->setSerializer(new CustomSerializer());
        $resource = new Item($bank_details, new BankDetailTransformer());

        $details = $manager->createData($resource)->toArray()['data'];

        return api_response($request, null, 200, ['data' => $details]);
    }

    public function updateMobileBankInformation($affiliate, ProfileMobileBankInformation $profile_mobile_bank_info, Request $request, ProfileMobileBankingRepositoryInterface $profile_mobile_bank_repo)
    {
        $affiliate = $request->affiliate;
        $this->setModifier($affiliate);
        $data = $request->except('affiliate', 'remember_token');
        $mobile_bank_details = $profile_mobile_bank_repo->update($profile_mobile_bank_info, $data);

        $manager = new Manager();
        $manager->setSerializer(new CustomSerializer());
        $resource = new Item($mobile_bank_details, new MobileBankDetailTransformer());

        $details = $manager->createData($resource)->toArray()['data'];

        return api_response($request, null, 200, ['data' => $details]);
    }

    public function deleteBankInformation($affiliate, $bank_info_id, Request $request, ProfileBankingRepositoryInterface $profile_bank_repo)
    {
        $affiliate = $request->affiliate;
        $this->setModifier($affiliate);
        $is_exist = $profile_bank_repo->find($bank_info_id);

        if ($is_exist) {
            $profile_bank_repo->delete($bank_info_id);
            return api_response($request, null, 200, ['msg' => 'deleted bank information']);
        }
        return api_response($request, null, 200, ['msg' => 'all ready delete']);
    }

    public function deleteMobileBankInformation($affiliate, $mobile_bank_info_id, Request $request, ProfileMobileBankingRepositoryInterface $profile_mobile_bank_repo)
    {
        $affiliate = $request->affiliate;
        $this->setModifier($affiliate);
        $is_exist = $profile_mobile_bank_repo->find($mobile_bank_info_id);
        if ($is_exist) {
            $profile_mobile_bank_repo->delete($mobile_bank_info_id);
            return api_response($request, null, 200, ['msg' => 'deleted bank information']);
        }
        return api_response($request, null, 200, ['msg' => 'all ready deleted']);
    }

    public function getPersonalInformation(Request $request)
    {
        $this->validate($request, [
            'mobile' => 'required|mobile:bd'
        ]);
        $profile = Profile::where('mobile', '+88' . $request->mobile)->first();
        if (is_null($profile)) return api_response($request, [], 404, null);

        $customer_name = $profile->name;
        $resource = Resource::where('profile_id', $profile->id)->first();
        if ($resource) {
            $token = $resource->remember_token;
            $resource_informations = [
                'name' => $profile->name,
                'image' => $profile->pro_pic,
                'resource' => [
                    'id' => $resource->id,
                    'token' => $token
                ]
            ];
            if (count($resource->partners) > 0) {
                $resource_informations['partner'] = [
                    'id' => $resource->partners[0]->id,
                    'name' => $resource->partners[0]->name,
                ];
                if ($resource->partners[0]->geo_informations) {
                    $resource_informations['partner']['lat'] = json_decode($resource->partners[0]->geo_informations)->lat;
                    $resource_informations['partner']['lng'] = json_decode($resource->partners[0]->geo_informations)->lng;
                    $resource_informations['partner']['radius'] = json_decode($resource->partners[0]->geo_informations)->radius;
                }
            }
            return api_response($request, $customer_name, 200, ['data' => $resource_informations]);
        } else {
            $resource_informations = [
                'name' => $profile->name,
                'image' => $profile->pro_pic,
            ];
            return api_response($request, $customer_name, 200, ['data' => $resource_informations]);
        }
    }

    public function bankList(Request $request)
    {
        $bank_list = GeneralBanking::getPublishedBank();
        return api_response($request, null, 200, ['data' => $bank_list]);
    }

    public function mobileBankList(Request $request)
    {
        $bank_list = MobileBanking::getPublishedBank();
        return api_response($request, null, 200, ['data' => $bank_list]);
    }

    private function mapAgents($query)
    {
        return collect(array_merge(...$query))->groupBy('id')
            ->map(function ($data) {
                $dataSet = $data[0];
                if (isset($data[1])) {
                    $dataSet['total_gifted_amount'] += $data[1]['total_gifted_amount'];
                    $dataSet['total_gifted_number'] += $data[1]['total_gifted_number'];
                }
                if (isset($data[2])) {
                    $dataSet['total_gifted_amount'] += $data[2]['total_gifted_amount'];
                    $dataSet['total_gifted_number'] += $data[2]['total_gifted_number'];
                }
                return $dataSet;
            })->values();
    }

    private function allAgents($affiliate)
    {
        return $affiliate->agents->map(function ($agent) {
            return [
                'id' => $agent->id,
                'profile_id' => $agent->profile_id,
                'name' => $agent->profile->name,
                'ambassador_id' => $agent->ambassador_id,
                'picture' => $agent->profile->pro_pic,
                'mobile' => $agent->profile->mobile,
                'created_at' => $agent->created_at->toDateTimeString(),
                'joined' => $agent->joined,
                'total_gifted_amount' => 0,
                'total_gifted_number' => 0
            ];
        })->toArray();
    }

    public function getOrderList($affiliate, Request $request)
    {
        $this->validate($request, [
            'filter' => 'sometimes|string|in:ongoing,history',
        ]);
        $filter = $request->filter;
        list($offset, $limit) = calculatePagination($request);
        $affiliate = $request->affiliate->load(['orders' => function ($q) use ($filter, $offset, $limit) {
            $q->select('orders.id', 'customer_id', 'partner_id', 'location_id', 'sales_channel', 'delivery_name', 'delivery_mobile', 'delivery_address', 'subscription_order_id')->where('sales_channel', constants('SALES_CHANNELS')['DDN']['name'])->orderBy('orders.id', 'desc')
                ->skip($offset)->take($limit);

            if ($filter) {
                $q->whereHas('partnerOrders', function ($q) use ($filter) {
                    $q->$filter();
                });
            }
            $q->with(['partnerOrders' => function ($q) use ($filter, $offset, $limit) {
                $q->with(['partner.resources.profile', 'order' => function ($q) {
                    $q->select('id', 'sales_channel', 'subscription_order_id');
                }, 'jobs' => function ($q) {
                    $q->with(['statusChangeLogs', 'resource.profile', 'jobServices', 'customerComplains', 'category', 'review' => function ($q) {
                        $q->select('id', 'rating', 'job_id');
                    }, 'usedMaterials']);
                    $q->with('jobServices.service');
                }]);
            }]);
        }]);
        if (count($affiliate->orders) > 0) {
            $all_jobs = $this->getInformation($affiliate->orders);
            $cancelled_served_jobs = $all_jobs->filter(function ($job) {
                return $job['cancelled_date'] != null || $job['status'] == 'Served';
            });
            $others = $all_jobs->diff($cancelled_served_jobs);
            $all_jobs = $others->merge($cancelled_served_jobs);

            $all_jobs->map(function ($job) {
                $order_job = Job::find($job['job_id']);
                $job['can_pay'] = $this->canPay($order_job);
                $job['can_take_review'] = $this->canTakeReview($order_job);
                return $job;
            });

        } else {
            $all_jobs = collect();
        }

        return count($all_jobs) > 0 ? api_response($request, $all_jobs, 200, ['orders' => $all_jobs->values()->all()]) : api_response($request, null, 404);
    }

    public function getOrderDetails($affiliate, $order, Request $request)
    {
        /** @var Job $job */
        $job = Job::find($order);
        if (empty($job)) return api_response($request, null, 404);
        $partner_order = $job->partner_order;
        $partner_order->calculate(true);
        $partner_order['total_paid'] = (double)$partner_order->paid;
        $partner_order['total_due'] = (double)$partner_order->due;
        $partner_order['total_price'] = (double)$partner_order->totalPrice;
        $partner_order['delivery_name'] = $partner_order->order->delivery_name;
        $partner_order['delivery_mobile'] = $partner_order->order->delivery_mobile;
        $partner_order['delivery_address'] = $partner_order->order->delivery_address_id ? $partner_order->order->deliveryAddress->address : $partner_order->order->delivery_address;
        $final = collect();
        foreach ($partner_order->jobs as $job) {
            $final->push($this->getJobInformation($job, $partner_order));
        }
        removeRelationsAndFields($partner_order);
        $partner_order['jobs'] = $final;
        return api_response($request, $partner_order, 200, ['orders' => $partner_order]);
    }

    private function getInformation($orders)
    {
        $all_jobs = collect();
        foreach ($orders as $order) {
            $partnerOrders = $order->partnerOrders;
            $cancelled_partnerOrders = $partnerOrders->filter(function ($o) {
                return $o->cancelled_at != null;
            })->sortByDesc('cancelled_at');
            $not_cancelled_partnerOrders = $partnerOrders->filter(function ($o) {
                return $o->cancelled_at == null;
            });
            $partnerOrder = $not_cancelled_partnerOrders->count() == 0 ? $cancelled_partnerOrders->first() : $not_cancelled_partnerOrders->first();
            $partnerOrder->calculate(true);
            if (!$partnerOrder->cancelled_at) {
                $job = ($partnerOrder->jobs->filter(function ($job) {
                    return $job->status !== 'Cancelled';
                }))->first();
            } else {
                $job = $partnerOrder->jobs->first();
            }
            if ($job != null) $all_jobs->push($this->getJobInformation($job, $partnerOrder));
        }
        return $all_jobs;
    }

    private function getJobInformation(Job $job, PartnerOrder $partnerOrder)
    {
        $category = $job->category;
        $job_service = $job->jobServices[0];
        $service = $job->jobServices[0]->service;
        $show_expert = $job->canCallExpert();
        $process_log = $job->statusChangeLogs->where('to_status', constants('JOB_STATUSES')['Process'])->first();
        return collect([
            'id' => $partnerOrder->id,
            'job_id' => $job->id,
            'subscription_order_id' => $partnerOrder->order->subscription_order_id,
            'category_name' => $category ? $category->name : null,
            'category_thumb' => $category ? $category->thumb : null,
            'service_id' => $service ? $service->id : null,
            'service_name' => $service ? $service->name : null,
            'service_thumb' => $service ? $service->thumb : null,
            'service_unit' => $service ? $service->unit : null,
            'job_quantity' => $job_service ? $job_service->quantity : null,
            'schedule_date' => $job->schedule_date ? $job->schedule_date : null,
            'served_date' => $job->delivered_date ? $job->delivered_date->format('Y-m-d H:i:s') : null,
            'process_date' => $process_log ? $process_log->created_at->format('Y-m-d H:i:s') : null,
            'cancelled_date' => $partnerOrder->cancelled_at,
            'schedule_date_readable' => (Carbon::parse($job->schedule_date))->format('M j, Y'),
            'preferred_time' => $job->preferred_time ? humanReadableShebaTime($job->preferred_time) : null,
            'readable_status' => constants('JOB_STATUSES_SHOW')[$job->status]['customer'],
            'status' => $job->status,
            'is_on_premise' => (int)$job->isOnPremise(),
            'customer_favorite' => !empty($job->customerFavorite) ? $job->customerFavorite->id : null,
            'isRentCar' => $job->isRentCar(),
            'status_color' => constants('JOB_STATUSES_COLOR')[$job->status]['customer'],
            'partner_name' => $partnerOrder->partner ? $partnerOrder->partner->name : null,
            'partner_logo' => $partnerOrder->partner ? $partnerOrder->partner->logo : null,
            'resource_name' => $job->resource ? $job->resource->profile->name : null,
            'resource_pic' => $job->resource ? $job->resource->profile->pro_pic : null,
            'contact_number' => $show_expert ? ($job->resource ? $job->resource->profile->mobile : null) : ($partnerOrder->partner ? $partnerOrder->partner->getManagerMobile() : null),
            'contact_person' => $show_expert ? 'expert' : 'partner',
            'rating' => $job->review != null ? $job->review->rating : null,
            'price' => (double)$partnerOrder->totalPrice,
            'order_code' => $partnerOrder->order->code(),
            'created_at' => $partnerOrder->created_at->format('Y-m-d'),
            'created_at_timestamp' => $partnerOrder->created_at->timestamp,
            'version' => $partnerOrder->getVersion(),
            'original_price' => (double)$partnerOrder->jobPrices + $job->logistic_charge,
            'discount' => (double)$partnerOrder->totalDiscount,
            'discounted_price' => (double)$partnerOrder->totalPrice + $job->logistic_charge,
            'complain_count' => $job->customerComplains->count(),
            'message' => (new JobLogs($job))->getOrderMessage(),
        ]);
    }

    protected function canPay($job)
    {
        $due = $job->partnerOrder->calculate(true)->due;
        $status = $job->status;

        if (in_array($status, ['Declined', 'Cancelled']))
            return false;
        else {
            return $due > 0;
        }
    }

    protected function canTakeReview($job)
    {
        $review = $job->review;

        if (!is_null($review) && $review->rating > 0) {
            return false;
        } else if ($job->partnerOrder->closed_at) {
            $closed_date = Carbon::parse($job->partnerOrder->closed_at);
            $now = Carbon::now();
            $difference = $closed_date->diffInDays($now);

            return $difference < constants('CUSTOMER_REVIEW_OPEN_DAY_LIMIT');
        } else {
            return false;
        }
    }
}
