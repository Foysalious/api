<?php

namespace App\Http\Controllers;

use App\Models\Affiliate;
use App\Models\AffiliateTransaction;
use App\Models\Affiliation;
use App\Repositories\NotificationRepository;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Validator;
use DB;

class AffiliationController extends Controller
{
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
            $request->merge(['mobile' => formatMobile($request->mobile)]);
            if ($msg = $this->_validateCreateRequest($request)) {
                return response()->json(['code' => 500, 'msg' => $msg]);
            }
            $affiliate = Affiliate::find($affiliate);
            if ($affiliate != null) {
                if ($affiliate->profile->mobile == $request->mobile) {
                    return response()->json(['code' => 501, 'msg' => "You can't refer yourself!"]);
                }
                if ($affiliate->verification_status != 'verified' || $affiliate->is_suspended == 1) {
                    return response()->json(['code' => 502, 'msg' => "You're not verified!"]);
                }
                $affiliation = new Affiliation();
                try {
                    DB::transaction(function () use ($request, $affiliate, $affiliation) {
                        $this->affiliationStore($request, $affiliate, $affiliation);
                        $this->affiliateWalletUpdate($affiliate);
                        $this->affiliateTransaction($affiliate, $affiliation);
                    });
                } catch (QueryException $q) {
                    $affiliation = null;
                }
                if ($affiliation != null) {
                    (new NotificationRepository())->forAffiliation($affiliate, $affiliation);
                    return api_response($request, 1, 200, ['massage'=> ['en' => 'Your refer have been submitted. You received 2TK bonus add in your wallet.', 'bd' => 'আপনার রেফারেন্সটি গ্রহন করা হয়েছে । আপনার ওয়ালেট ২ টাকা বোনাস যোগ করা হয়েছে।']]);
                } else {
                    return api_response($request, null, 500);
                }
            } else {
                return api_response($request, null, 404);
            }
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    private function _validateCreateRequest($request)
    {
        $validator = Validator::make($request->all(), [
            'mobile' => 'required|string|mobile:bd',
        ], ['mobile' => 'Invalid mobile number!']);
        return $validator->fails() ? $validator->errors()->all()[0] : false;
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
        $affiliate->wallet += 2;
        $affiliate->update();
    }

    private function affiliateTransaction($affiliate, $affiliation)
    {
        $affiliate_transaction = new AffiliateTransaction();
        $affiliate_transaction->affiliate_id = $affiliate->id;
        $affiliate_transaction->affiliation_id = $affiliation->id;
        $affiliate_transaction->type = "Credit";
        $affiliate_transaction->log = "You have received 2 tk";
        $affiliate_transaction->amount = 2;
        $affiliate_transaction->save();
    }

}
