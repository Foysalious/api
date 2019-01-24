<?php namespace Sheba\PartnerAffiliation;

use App\Helper\BangladeshiMobileValidator;
use App\Models\PartnerAffiliation;
use App\Models\Profile;
use Illuminate\Http\Request;

class PartnerAffiliationCreateValidator
{
    private $data;

    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }

    public function hasError()
    {
        if ($error = $this->hasNecessaryFields()) return $error;
        if ($error = $this->isResourceExist()) return $error;
        if ($error = $this->isOngoingLead()) return $error;
        return false;
    }

    private function hasKey($key)
    {
        return array_key_exists($key, $this->data);
    }

    private function hasNecessaryFields()
    {
        if (!$this->hasKey('resource_mobile')) return ['code' => 400, 'msg' => ['en' => 'Mobile number is mandatory', 'bd' => 'মোবাইল নং প্রদান করা আবর্শক']];
        if (!$this->hasKey('resource_name')) return ['code' => 400, 'msg' => ['en' => 'Name is mandatory', 'bd' => 'নাম প্রদান আবর্শক']];
        if (!$this->hasKey('company_name')) return ['code' => 400, 'msg' => ['en' => 'Company name is mandatory', 'bd' => 'কোম্পানি নাম প্রদান আবর্শক']];
        return false;
    }

    private function isResourceExist()
    {
        if (!BangladeshiMobileValidator::validate($this->data['resource_mobile']))
            return ['code' => 400, 'msg' => ['en' => 'Number format does not match', 'bd' => 'Number format does not match']];
        $profile = Profile::where('mobile', formatMobile($this->data['resource_mobile']))->first();
        if($profile && $profile->resource)
                return ['code' => 400, 'msg' => ['en' => 'Sorry! your referral number already exists.', 'bd' => 'দুঃখিত !!! আপনার রেফার ক্রিত নাম্বারটি সেবাতে নিবন্ধিত।']];
        return false;
    }

    private function isOngoingLead()
    {
        $partner_affiliation = PartnerAffiliation::where('resource_mobile', formatMobile($this->data['resource_mobile']))
            ->where(function ($q) {
                $q->whereIn('status', [PartnerAffiliationStatuses::$successful, PartnerAffiliationStatuses::$pending])
                    ->orWhere(function ($q) {
                        $q->whereIn('reject_reason', PartnerAffiliationRejectReasons::fake())
                            ->where('status', PartnerAffiliationStatuses::$rejected);
                    });
            })->first();
        if ($partner_affiliation) return ['code' => 400, 'msg' => ['en' => 'Sorry! your referral number already referred', 'bd' => 'দুঃখিত !!! আপনার রেফার ক্রিত নাম্বারটি সেবাতে নিবন্ধিত।']];
        return false;
    }
}