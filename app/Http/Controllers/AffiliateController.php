<?php

namespace App\Http\Controllers;

use App\Models\Affiliate;
use App\Models\AffiliateTransaction;
use App\Models\Affiliation;
use App\Models\PartnerTransaction;
use App\Models\Service;
use App\Repositories\AffiliateRepository;
use App\Repositories\FileRepository;
use App\Repositories\LocationRepository;
use App\Sheba\Bondhu\AffiliateHistory;
use App\Sheba\Bondhu\AffiliateStatus;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Sheba\PartnerPayment\PartnerPaymentValidatorFactory;
use Validator;
use DB;

class AffiliateController extends Controller
{
    private $fileRepository;
    private $locationRepository;
    private $affiliateRepository;

    public function __construct()
    {
        $this->fileRepository = new FileRepository();
        $this->locationRepository = new LocationRepository();
        $this->affiliateRepository = new AffiliateRepository();
    }

    public function edit($affiliate, Request $request)
    {
        try {
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
            if ($request->has('name')) {
                $profile = $affiliate->profile;
                $profile->name = $request->name;
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
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function getStatus($affiliate, Request $request)
    {
        try {
            $affiliate = Affiliate::where('id', $affiliate)->select('verification_status', 'is_suspended', 'ambassador_code', 'is_ambassador')->first();
            return $affiliate != null ? response()->json(['code' => 200, 'affiliate' => $affiliate]) : response()->json(['code' => 404, 'msg' => 'Not found!']);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function getDashboardInfo($affiliate, Request $request)
    {
        try {
            $affiliate = Affiliate::find($affiliate);
            $info = [
                'wallet' => (double)$affiliate->wallet,
                'total_income' => (double)$affiliate->transactions->where('type', 'Credit')->sum('amount'),
                'total_service_referred' => $affiliate->affiliations->count(),
                'total_sp_referred' => $affiliate->partnerAffiliations->count(),
                'last_updated' => Carbon::parse($affiliate->updated_at)->format('dS F,g:i A')
            ];
            return api_response($request, $info, 200, ['info' => $info]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function updateProfilePic(Request $request)
    {
        try {
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
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }

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
            'sp_type' => 'required|in:affiliates,partner_affiliates'
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $error = $validator->errors()->all()[0];
            return api_response($request, $error, 400, ['msg' => $error]);
        }
        if ($request->agent_data)
            $status = $status->setType($request->sp_type)->getFormattedDate($request)->getAgentsData($affiliate);
        else
            $status = $status->setType($request->sp_type)->getFormattedDate($request)->getIndividualData($affiliate);

        return response()->json(['code' => 200, 'data' => $status]);
    }

    public function getAmbassador($affiliate, Request $request)
    {
        try {
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
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function joinClan($affiliate, Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'code' => 'required|string'
            ]);
            if ($validator->fails()) {
                $error = $validator->errors()->all()[0];
                return api_response($request, $error, 400, ['msg' => $error]);
            }
            $affiliate = $request->affiliate;
            if ($affiliate->is_ambassador == 1 || $affiliate->ambassador_id != null) {
                return api_response($request, null, 403);
            }
            $ambassador = Affiliate::where([
                ['ambassador_code', strtoupper(trim($request->code))],
                ['id', '<>', $affiliate->id],
                ['is_ambassador', 1]
            ])->first();
            if ($ambassador) {
                $affiliate = $request->affiliate;
                $affiliate->ambassador_id = $ambassador->id;
                $affiliate->under_ambassador_since = Carbon::now();
                $affiliate->update();
                return api_response($request, $ambassador, 200);
            } else {
                return api_response($request, null, 404);
            }
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function getAgents($affiliate, Request $request)
    {
        $affiliate = $request->affiliate;
        try {
            if ($affiliate->is_ambassador == 0) {
                return api_response($request, null, 403);
            }
            $q = $request->get('query');
            $range = $request->get('range');
            list($offset, $limit) = calculatePagination($request);
            if (isset($range) && !empty($range)) {
                $query = Affiliate::agentsWithFilter($request);
            } else {
                $query = Affiliate::agentsWithoutFilter($request);
            }
            if (isset($q)) {
                $query->where('profiles.name', 'LIKE', $q . '%');
            }
            $agents = $query->skip($offset)
                ->take($limit)->get();
            if (count($agents) > 0) {
                $response = ['agents' => $agents];
                if ($range) {
                    $r_d = getRangeFormat($request);
                    $response['range'] = ['to' => $r_d[0], 'from' => $r_d[1]];
                }
                return api_response($request, $agents, 200, $response);
            }
            return api_response($request, null, 404);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function getGodFather($affiliate, Request $request)
    {
        try {
            $affiliate = $request->affiliate;
            if ($affiliate->ambassador_id == null) {
                return api_response($request, null, 404);
            } else {
                $profile = collect($affiliate->ambassador->profile)->only(['name', 'pro_pic', 'mobile'])->all();
                $profile['picture'] = $profile['pro_pic'];
                array_forget($profile, 'pro_pic');
                return api_response($request, $profile, 200, ['info' => $profile]);
            }
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function getLeaderboard($affiliate, Request $request)
    {
        try {
            list($offset, $limit) = calculatePagination($request);
            $transactions = AffiliateTransaction::with(['affiliate' => function ($q) {
                $q->with(['profile' => function ($q) {
                    $q->select('id', 'name', 'pro_pic');
                }, 'affiliations' => function ($q) {
                    $q->selectRaw('count(*) as total_reference, affiliate_id')->where('status', 'successful')->groupBy('affiliate_id');
                }]);
            }])->selectRaw('sum(amount) as earning_amount, affiliate_id')
                ->where('type', 'Credit')->groupBy('affiliate_id')->orderBy('earning_amount', 'desc')->skip($offset)->take($limit)->get();
            $final = [];
            foreach ($transactions as $transaction) {
                $info['id'] = $transaction->affiliate->id;
                $info['earning_amount'] = (double)$transaction->earning_amount;
                $info['total_reference'] = $transaction->affiliate->affiliations->first()->total_reference;
                $info['name'] = $transaction->affiliate->profile->name;
                $info['picture'] = $transaction->affiliate->profile->pro_pic;
                array_push($final, $info);
            }
            return count($final) != 0 ? api_response($request, $final, 200, ['affiliates' => $final]) : api_response($request, null, 404);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function getAmbassadorSummary($affiliate, Request $request)
    {
        try {
            $affiliate = $request->affiliate;
            if ($affiliate->is_ambassador == 0) {
                return api_response($request, null, 403);
            }
            $info = collect();
            $info->put('agent_count', $affiliate->agents->count());
            $info->put('earning_amount', $affiliate->agents->sum('total_gifted_amount'));
            $info->put('total_refer', Affiliation::whereIn('affiliate_id', $affiliate->agents->pluck('id')->toArray())->count());
            $info->put('sp_count', $affiliate->partnerAffiliations->count());
            return api_response($request, $info, 200, ['info' => $info->all()]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function getTransactions($affiliate, Request $request)
    {
        try {
            $affiliate = $request->affiliate;
            $affiliate->load(['transactions' => function ($q) {
                $q->select('id', 'affiliate_id', 'affiliation_id', 'type', 'log', 'amount', DB::raw('DATE_FORMAT(created_at, "%M %d, %Y at %h:%i %p") as time'))->orderBy('id', 'desc');
            }]);
            if ($affiliate->transactions != null) {
                $transactions = $affiliate->transactions;
                $credit = $transactions->where('type', 'Credit')->values()->all();
                $debit = $transactions->where('type', 'Debit')->values()->all();
                return api_response($request, $affiliate->transactions, 200, ['credit' => $credit, 'debit' => $debit]);
            } else {
                return api_response($request, null, 404);
            }
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function getNotifications($affiliate, Request $request)
    {
        try {
            list($offset, $limit) = calculatePagination($request);
            $notifications = $request->affiliate->notifications()->select('id', 'title', 'event_type', 'event_id', 'type', 'is_seen', 'created_at')->orderBy('id', 'desc')->skip($offset)->limit($limit)->get();
            if (count($notifications) > 0) {
                $notifications = $notifications->map(function ($notification) {
                    $notification->event_type = str_replace('App\Models\\', "", $notification->event_type);
                    array_add($notification, 'timestamp', $notification->created_at->timestamp);
                    return $notification;
                });
                return api_response($request, $notifications, 200, ['notifications' => $notifications]);
            } else {
                return api_response($request, null, 404);
            }
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function rechargeWallet($affiliate, Request $request)
    {
        try {
            $this->validate($request, [
                'transaction_id' => 'required|string',
                'type' => 'required|in:bkash',
            ]);
            $payment_validator = PartnerPaymentValidatorFactory::make($request->all());
            if ($error = $payment_validator->hasError()) return api_response($request, null, 400, ['message' => $error]);
            $affiliate = $request->affiliate;
            if ($this->ifTransactionAlreadyExists($request->transaction_id)) return api_response($request, null, 403, ['message' => 'Transaction id already exists']);
            $this->recharge($affiliate, $payment_validator);
            return api_response($request, null, 200, ['message' => "Moneybag refilled."]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    private function ifTransactionAlreadyExists($transaction_id)
    {
        return (AffiliateTransaction::where('transaction_details', 'like', "%$transaction_id%")->count() > 0) || (PartnerTransaction::where('transaction_details', 'like', "%$transaction_id%")->count() > 0);
    }

    private function recharge(Affiliate $affiliate, $payment_validator)
    {
        $data = $this->makeRechargeData($payment_validator);
        $amount = $payment_validator->amount;
        DB::transaction(function () use ($amount, $affiliate, $data) {
            $affiliate->rechargeWallet($amount, $data);
        });
    }

    private function makeRechargeData($payment_validator)
    {
        return [
            'amount' => $payment_validator->amount,
            'transaction_details' => json_encode(
                [
                    'name' => 'Bkash',
                    'details' => [
                        'transaction_id' => $payment_validator->response->transaction->trxId,
                        'gateway' => 'bkash',
                        'details' => $payment_validator->response
                    ]
                ]
            ),
            'type' => 'Credit', 'log' => 'Moneybag Refilled'
        ];
    }

    private function _validateImage($request)
    {
        $validator = Validator::make($request->all(), [
            'photo' => 'required|mimes:jpeg,png|max:500'
        ]);
        return $validator->fails() ? $validator->errors()->all()[0] : false;
    }

    private function _validateEdit($request)
    {
        $validator = Validator::make($request, [
            'bkash_no' => 'sometimes|required|string|mobile:bd',
        ], ['mobile' => 'Invalid bKash number!']);
        return $validator->fails() ? $validator->errors()->all()[0] : false;
    }

    public function getServicesInfo($affiliate, Request $request)
    {
        try {
            list($offset, $limit) = calculatePagination($request);
            $services = Service::PublishedForBondhu()->skip($offset)->take($limit)->get()->map(function ($service) {
                return [
                    'id' => $service->id,
                    'name' => $service->name,
                    'bangla_name' => empty($service->bn_name) ? null : $service->bn_name,
                    'image' => $service->app_thumb
                ];
            });
            return api_response($request, $services, 200, ['services' => $services]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
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
        $historyData = $history->setType($request->sp_type)->getFormattedDate($request)->generateData($affiliate,$request->agent_id)->skip($offset)->take($limit)->get();
        return response()->json(['code' => 200, 'data' => $historyData]);
    }
}
