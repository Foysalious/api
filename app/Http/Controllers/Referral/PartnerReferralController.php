<?php namespace App\Http\Controllers\Referral;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Sheba\Referral\Referrals;

class PartnerReferralController extends Controller
{
    public function index(Request $request)
    {
       try{
           $partner  = $request->partner;
           $reference    = Referrals::getReference($partner);
           $referrals=$reference->getReferrals();
           return api_response($request,$reference->refers, 200,['data'=>$referrals]);
       }catch (\Throwable $e){
           dd($e);
       }
    }

    public function setReference() { }

    public function referLinkGenerate() { }

    public function earnings() { }

    public function details() { }
}
