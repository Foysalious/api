<?php

namespace App\Http\Controllers;

use App\Models\Affiliate;
use App\Models\AffiliateTransaction;
use App\Models\Affiliation;
use App\Repositories\NotificationRepository;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Validator;
use DB;

class AffiliationController extends Controller
{
    private $acquisitionMoney;

    public function __construct()
    {
        $this->acquisitionMoney = constants('AFFILIATION_ACQUISITION_MONEY');
    }

    public function newIndex($affiliate, Request $request)
    {
        list($offset, $limit) = calculatePagination($request);
        $affiliate = Affiliate::with(['affiliations' => function ($q) use ($offset, $limit, $affiliate) {
            $q->select('id', 'affiliate_id', 'customer_name', 'customer_mobile', 'service', 'status', 'is_fake', 'reject_reason', DB::raw('DATE_FORMAT(created_at, "%Y-%m-%d") as referred_date'))
                ->with(['transactions' => function ($q) use ($affiliate) {
                    $q->where([
                        ['type', 'Credit'],
                        ['affiliate_id', $affiliate]
                    ]);
                }])->orderBy('id', 'desc')
                ->skip($offset)->take($limit);
        }])->select('id')->where('id', $affiliate)->first();
        if (count($affiliate->affiliations) != 0) {
            $affiliations = $affiliate->affiliations;
            foreach ($affiliate->affiliations as $affiliation) {
                if ($affiliation->transactions != null) {
                    array_add($affiliation, 'earning_amount', $affiliation->transactions->sum('amount'));
                } else {
                    array_add($affiliation, 'earning_amount', 0);
                }
                array_forget($affiliation, 'transactions');
            }
            return api_response($request, $affiliate->affiliations, 200, ['affiliations' => $affiliations]);
        } else {
            return api_response($request, null, 404);
        }
    }

    public function index($affiliate, Request $request)
    {
        $offset = 0;
        if ($request->get('page') != '') {
            $offset = 12;
            $offset = ($request->get('page') - 1) * $offset;
        }
        $affiliate = Affiliate::with(['affiliations' => function ($q) use ($offset) {
            $q->select('id', 'affiliate_id', 'customer_name', 'customer_mobile', 'service', 'status', 'is_fake', 'reject_reason', DB::raw('DATE_FORMAT(created_at, "%Y-%m-%d") as referred_date'))
                ->with(['transactions' => function ($q) {
                    $q->where('type', 'Credit');
                }])->orderBy('id', 'desc')
                ->skip($offset)->take(12);
        }])->select('id')->where('id', $affiliate)->first();
        if (count($affiliate->affiliations) != 0) {
            $affiliations = $affiliate->affiliations;
            foreach ($affiliate->affiliations as $affiliation) {
                if ($affiliation->transactions != null) {
                    array_add($affiliation, 'earning_amount', $affiliation->transactions->sum('amount'));
                } else {
                    array_add($affiliation, 'earning_amount', 0);
                }
                array_forget($affiliation, 'transactions');
            }
            return api_response($request, $affiliate->affiliations, 200, ['affiliations' => $affiliations]);
        } else {
            return api_response($request, null, 404);
        }
    }

    public function create($affiliate, Request $request)
    {
        try {
            $request->merge(['mobile' => formatMobile(trim($request->mobile))]);
            $this->validate($request, ['mobile' => 'required|string|mobile:bd'], ['mobile' => 'Invalid mobile number!']);
            if ($affiliate = Affiliate::where([['id', $affiliate], ['verification_status', 'verified'], ['is_suspended', 0]])->first()) {
                if ($affiliate->profile->mobile == $request->mobile) {
                    return response()->json(['code' => 501, 'msg' => "You can't refer to yourself!"]);
                }
                $affiliation_counter = Affiliation::where('affiliate_id', $affiliate->id)->where('created_at', '>=', Carbon::today())->count();
                if ($affiliation_counter < 20) {
                    $affiliation = new Affiliation();
                    try {
                        DB::transaction(function () use ($request, $affiliate, $affiliation) {
                            $this->affiliationStore($request, $affiliate, $affiliation);
                            $this->affiliateWalletUpdate($affiliate);
                            $this->affiliateTransaction($affiliate, $affiliation);
                        });
                    } catch (QueryException $e) {
                        app('sentry')->captureException($e);
                        return api_response($request, null, 500);
                    }
                    (new NotificationRepository())->forAffiliation($affiliate, $affiliation);
                    $message = ['en' => 'Your refer have been submitted. You received 2TK bonus add in your wallet.', 'bd' => 'আপনার রেফারেন্সটি গ্রহন করা হয়েছে । আপনার ওয়ালেটে ২ টাকা বোনাস যোগ করা হয়েছে।'];
                    return api_response($request, 1, 200, ['massage' => $message]);
                } else {
                    $message = ['en' => 'Your referral limit already exceeded please try again tomorrow.', 'bd' => 'দুঃখিত! আপনার সর্বোচ্চ রেফার সংখ্যা অতিক্রম করেছে। অনুগ্রহ করে আগামিকাল চেষ্টা করুন।'];
                    return api_response($request, null, 403, ['massage' => $message]);
                }
            } else {
                return api_response($request, null, 502);
            }
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    private function affiliationStore($request, $affiliate, $affiliation)
    {
        $affiliation->affiliate_id = $affiliate->id;
        $affiliation->customer_name = $request->name;
        $affiliation->customer_mobile = $request->mobile;
        $affiliation->service = $request->service;

        $affiliation->save();
    }

    private function affiliateWalletUpdate($affiliate)
    {
        $affiliate->wallet += $this->acquisitionMoney;
        $affiliate->update();
    }

    private function affiliateTransaction($affiliate, $affiliation)
    {
        $affiliate_transaction = new AffiliateTransaction();
        $affiliate_transaction->affiliate_id = $affiliate->id;
        $affiliate_transaction->affiliation_id = $affiliation->id;
        $affiliate_transaction->type = "Credit";
        $affiliate_transaction->log = "Earned $this->acquisitionMoney tk for giving reference id: $affiliation->id";
        $affiliate_transaction->amount = $this->acquisitionMoney;
        $affiliate_transaction->save();
    }

}
