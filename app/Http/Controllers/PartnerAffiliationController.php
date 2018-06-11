<?php namespace App\Http\Controllers;

use App\Models\PartnerAffiliation;
use Illuminate\Http\Request;
use App\Http\Requests;
use Sheba\PartnerAffiliation\PartnerAffiliationCreateValidator;
use Sheba\PartnerAffiliation\PartnerAffiliationCreator;
use Sheba\PartnerAffiliation\PartnerAffiliationRejectReasons;

class PartnerAffiliationController extends Controller
{
    public function store(Request $request)
    {
        if ($error = (new PartnerAffiliationCreateValidator)->validate($request)) return api_response($request, null, $error['code'], ["msg" => $error['msg']]);

        (new PartnerAffiliationCreator)->create($request->all());

        $message = ['en' => 'Your refer have been submitted.', 'bd' => 'আপনার রেফারেন্সটি গ্রহন করা হয়েছে ।'];
        return api_response($request, null, 200, ["msg" => $message]);
    }

    public function index(Request $request)
    {
        $partner_affiliations = PartnerAffiliation::with(['transactions' => function ($q) {
            $q->where('type', 'Credit');
        }])->where('affiliate_id', $request->affiliate->id)->get();
        $data = [];
        foreach ($partner_affiliations as $partner_affiliation) {
            $data[] = [
                'id'                => $partner_affiliation->id,
                'affiliate_id'      => $partner_affiliation->affiliate_id,
                'company_name'      => $partner_affiliation->company_name,
                'resource_name'     => $partner_affiliation->resource_name,
                'resource_mobile'   => $partner_affiliation->resource_mobile,
                'status'            => $partner_affiliation->status,
                'is_fake'           => ($partner_affiliation->reject_reason == PartnerAffiliationRejectReasons::fake()),
                'reject_reason'     => $partner_affiliation->reject_reason,
                'referred_date'     => $partner_affiliation->created_at->format('Y-m-d'),
                'earning_amount'    => $partner_affiliation->transactions->sum('amount')
            ];
        }

        return api_response($request, null, 200, ['affiliations' => $data]);
    }
}
