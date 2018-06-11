<?php namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use Sheba\PartnerAffiliation\PartnerAffiliationCreateValidator;
use Sheba\PartnerAffiliation\PartnerAffiliationCreator;

class PartnerAffiliationController extends Controller
{
    public function store(Request $request)
    {
        if ($error = (new PartnerAffiliationCreateValidator)->validate($request)) return api_response($request, null, $error['code'], ["msg" => $error['msg']]);

        (new PartnerAffiliationCreator)->create($request->all());

        $message = ['en' => 'Your refer have been submitted.', 'bd' => 'আপনার রেফারেন্সটি গ্রহন করা হয়েছে ।'];
        return api_response($request, null, 200, ["msg" => $message]);
    }
}
