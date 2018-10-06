<?php

namespace App\Http\Controllers;

use App\Models\Affiliate;
use App\Models\AffiliateTransaction;
use App\Repositories\AffiliateRepository;
use App\Repositories\FileRepository;
use App\Repositories\LocationRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;
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
        } catch ( \Throwable $e ) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function getStatus($affiliate, Request $request)
    {
        try {
            $affiliate = Affiliate::where('id', $affiliate)->select('verification_status', 'is_suspended', 'ambassador_code', 'is_ambassador')->first();
            return $affiliate != null ? response()->json(['code' => 200, 'affiliate' => $affiliate]) : response()->json(['code' => 404, 'msg' => 'Not found!']);
        } catch ( \Throwable $e ) {
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
        } catch ( \Throwable $e ) {
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
        } catch ( \Throwable $e ) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }

    }

    public function getWallet($affiliate, Request $request)
    {
        $affiliate = Affiliate::find($affiliate);
        return $affiliate != null ? response()->json(['code' => 200, 'wallet' => $affiliate->wallet]) : response()->json(['code' => 404, 'msg' => 'Not found!']);
    }

    public function leadInfo($affiliate, Request $request)
    {
        $affiliate = Affiliate::find($affiliate);
        return response()->json(['code' => 200, 'total_lead' => $affiliate->totalLead(), 'earning_amount' => $affiliate->earningAmount()]);
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
        } catch ( \Throwable $e ) {
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
        } catch ( \Throwable $e ) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function getAgents($affiliate, Request $request)
    {
        try {
            $affiliate = $request->affiliate;
            if ($affiliate->is_ambassador == 0) {
                return api_response($request, null, 403);
            }
            $affiliate->load(['agents' => function ($q) {
                $q->select('id', 'profile_id', 'ambassador_id', 'total_gifted_number', 'total_gifted_amount')->with(['profile' => function ($q) {
                    $q->select('id', 'name', 'pro_pic', 'mobile');
                }]);
            }]);
            if (count($affiliate->agents) != 0) {
                list($offset, $limit) = calculatePagination($request);
                $agents = $affiliate->agents->splice($offset, $limit)->all();
                if (count($agents) != 0) {
                    foreach ($agents as $agent) {
                        $agent['name'] = $agent->profile->name;
                        $agent['picture'] = $agent->profile->pro_pic;
                        $agent['mobile'] = $agent->profile->mobile;
                        $agent['total_gifted_amount'] = (double)$agent->total_gifted_amount;
                        array_forget($agent, 'profile');
                        array_forget($agent, 'ambassador_id');
                        array_forget($agent, 'profile_id');
                    }
                    $agents = $this->affiliateRepository->sortAgents($request, $agents);
                    return api_response($request, $agents, 200, ['agents' => $agents]);
                }
            }
            return api_response($request, null, 404);
        } catch ( \Throwable $e ) {
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
        } catch ( \Throwable $e ) {
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
        } catch ( \Throwable $e ) {
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
            $info->put('total_refer', $affiliate->agents->sum('total_gifted_number'));
            return api_response($request, $info, 200, ['info' => $info->all()]);
        } catch ( \Throwable $e ) {
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
        } catch ( \Throwable $e ) {
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
        } catch ( \Throwable $e ) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
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


}
