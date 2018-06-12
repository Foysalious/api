<?php namespace Sheba\PartnerAffiliation;

use App\Helper\BangladeshiMobileValidator;
use App\Models\PartnerAffiliation;
use App\Models\Profile;
use Illuminate\Http\Request;

class PartnerAffiliationCreateValidator
{
    public function validate(Request $request)
    {
        if ($error = $this->hasNecessaryFields($request)) return $error;
        if ($error = $this->isResourceExist($request)) return $error;
        if ($error = $this->isOngoingLead($request)) return $error;
        return false;
    }

    private function hasNecessaryFields(Request $request)
    {
        if (!$request->has('resource_mobile')) return ['code' => 400, 'msg' => 'Resource mobile does not found'];
        if (!$request->has('resource_name')) return ['code' => 400, 'msg' => 'Resource name does not found'];
        if (!$request->has('company_name')) return ['code' => 400, 'msg' => 'Company name does not found'];
        return false;
    }

    private function isResourceExist(Request $request)
    {
        if (!BangladeshiMobileValidator::validate($request->resource_mobile)) return ['code' => 400, 'msg' => 'Number format does not match'];
        $profile = Profile::where('mobile', formatMobile($request->resource_mobile))->first();
        if($profile) {
            if ($profile->resource) return ['code' => 400, 'msg' => 'Partner already exist'];
        }
        return false;
    }

    private function isOngoingLead(Request $request)
    {
        $partner_affiliation = PartnerAffiliation::where('resource_mobile', formatMobile($request->resource_mobile))
            ->whereIn('status', [PartnerAffiliationStatuses::$successful, PartnerAffiliationStatuses::$pending])
            ->orWhere(function ($q) {
                $q->whereIn('reject_reason', PartnerAffiliationRejectReasons::fake())
                    ->where('status', PartnerAffiliationStatuses::$rejected);
            })->first();
        if ($partner_affiliation) return ['code' => 400, 'msg' => 'Invalid resource number'];
        return false;
    }
}