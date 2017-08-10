<?php

namespace App\Http\Controllers;

use App\Models\Affiliate;
use App\Models\Affiliation;
use App\Repositories\NotificationRepository;
use Illuminate\Http\Request;
use Validator;
use DB;

class AffiliationController extends Controller
{
    public function index($affiliate, Request $request)
    {
        $offset = 0;
        if ($request->get('page') != '') {
            $offset = 12;
            $offset = ($request->get('page') - 1) * $offset;
        }
        $affiliate = Affiliate::with(['affiliations' => function ($q) use ($offset) {
            $q->select('id', 'affiliate_id', 'customer_name', 'customer_mobile', 'service', 'status', 'is_fake', 'reject_reason', DB::raw('DATE_FORMAT(created_at, "%Y-%m-%d") as referred_date'))
                ->orderBy('id', 'desc')
                ->skip($offset)->take(12);
        }])->select('id')->where('id', $affiliate)->first();
        return count($affiliate->affiliations) > 0 ? response()->json(['code' => 200, 'affiliations' => $affiliate->affiliations]) : response()->json(['code' => 404]);
    }

    public function create($affiliate, Request $request)
    {
        $request->merge(['mobile' => formatMobile($request->mobile)]);
        if ($msg = $this->_validateCreateRequest($request)) {
            return response()->json(['code' => 500, 'msg' => $msg]);
        }
        $affiliate = Affiliate::find($affiliate);
        if ($affiliate != null) {
            if ($affiliate->profile->mobile == $request->mobile) {
                return response()->json(['code' => 501, 'msg' => "You can't reffer yourself!"]);
            }
            if ($affiliate->verification_status != 'verified' || $affiliate->is_suspended == 0) {
                return response()->json(['code' => 500, 'msg' => "You're not verified!"]);
            }
            $affiliation = new Affiliation();
            $affiliation->affiliate_id = $affiliate->id;
            $affiliation->customer_name = $request->name;
            $affiliation->customer_mobile = $request->mobile;
            $affiliation->service = $request->service;
            if ($affiliation->save()) {
                (new NotificationRepository())->forAffiliation($affiliate, $affiliation);
                return response()->json(['code' => 200]);
            } else {
                return response()->json(['code' => 404]);
            }
        } else {
            return response()->json(['code' => 404]);
        }
    }

    private function _validateCreateRequest($request)
    {
        $validator = Validator::make($request->all(), [
            'mobile' => 'required|string|mobile:bd',
        ], ['mobile' => 'Invalid mobile number!']);
        return $validator->fails() ? $validator->errors()->all()[0] : false;
    }

}
