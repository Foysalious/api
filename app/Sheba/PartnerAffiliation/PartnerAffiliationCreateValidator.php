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
        if (!$request->has('resource_mobile')) return ['code' => 400, 'msg' => ['en' => 'Resource mobile does not found', 'bd' => 'Resource mobile does not found']];
        if (!$request->has('resource_name')) return ['code' => 400, 'msg' => ['en' => 'Resource name does not found', 'bd' => 'Resource name does not found']];
        if (!$request->has('company_name')) return ['code' => 400, 'msg' => ['en' => 'Company name does not found', 'bd' => 'Company name does not found']];
        return false;
    }

    private function isResourceExist(Request $request)
    {
        if (!BangladeshiMobileValidator::validate($request->resource_mobile)) return ['code' => 400, 'msg' => ['en' => 'Number format does not match', 'bd' => 'Number format does not match']];
        $profile = Profile::where('mobile', formatMobile($request->resource_mobile))->first();
        if($profile) {
            if ($profile->resource) return ['code' => 400, 'msg' => ['en' => 'Partner already exist', 'bd' => 'Partner already exist']];
        }
        return false;
    }

    private function isOngoingLead(Request $request)
    {
        $partner_affiliation = PartnerAffiliation::where('resource_mobile', formatMobile($request->resource_mobile))
            ->where(function ($q) {
                $q->whereIn('status', [PartnerAffiliationStatuses::$successful, PartnerAffiliationStatuses::$pending])
                    ->orWhere(function ($q) {
                        $q->whereIn('reject_reason', PartnerAffiliationRejectReasons::fake())
                            ->where('status', PartnerAffiliationStatuses::$rejected);
                    });
            })->first();
        if ($partner_affiliation) return ['code' => 400, 'msg' => ['en' => 'Partner already referred', 'bd' => 'Partner already referred']];
        return false;
    }
}