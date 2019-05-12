<?php namespace Sheba\Reports\Affiliate;

use App\Models\Affiliate;
use Sheba\Dal\AffiliateReport\AffiliateReport;
use Sheba\Reports\Presenter as BasePresenter;

class Presenter extends BasePresenter
{
    /** @var Affiliate */
    private $affiliate;
    /** @var AffiliateReport */
    private $affiliateReport;

    public function setAffiliate(Affiliate $affiliate)
    {
        $this->affiliate = $affiliate;
        return $this;
    }

    public function setAffiliateReport(AffiliateReport $affiliate_report)
    {
        $this->affiliateReport = $affiliate_report;
        return $this;
    }

    /** @return array */
    public function get()
    {
        $this->affiliateReport = $this->affiliateReport ?: $this->getFromAffiliate();
        return $this->affiliateReport->toArray();
    }

    private function getFromAffiliate()
    {
        return new AffiliateReport([
            'id' => $this->affiliate->id,
            'name' => $this->affiliate->profile->name,
            'mobile' => $this->affiliate->profile->mobile,
            'email' => $this->affiliate->profile->email,
            'address' => $this->affiliate->profile->address,
            'gender' => $this->affiliate->profile->gender,
            'tags' => !$this->affiliate->tag_names->isEmpty() ? $this->affiliate->tag_names->implode(',') : 'N/A',
            'is_ambassador' => $this->affiliate->is_ambassador,
            'has_ambassador' => $this->affiliate->ambassador_id,
            'ambassador_id' => $this->affiliate->ambassador_id,
            'ambassador_name' => $this->affiliate->ambassador_id ? $this->affiliate->ambassador->profile->name : null,
            'ambassador_mobile' => $this->affiliate->ambassador_id ? $this->affiliate->ambassador->profile->mobile : null,
            'under_ambassador_since' => $this->affiliate->ambassador_id ? $this->affiliate->under_ambassador_since : null,
            'store_name' => $this->affiliate->store_name,
            'location' => $this->affiliate->location_id ? $this->affiliate->location->name : null,
            'wallet' => (float)$this->affiliate->wallet,
            'payment_amount_this_week' => (float)$this->affiliate->payment_amount,
            'total_referred' => $this->affiliate->affiliations->count() + $this->affiliate->partnerAffiliations->count(),
            'successfully_referred' => $this->affiliate->affiliations->where('status', 'successful')->count(),
            'total_earned' => $aa=$this->affiliate->transactions->where('type', 'Credit')->sum('amount'),
            'total_paid' => $this->affiliate->transactions->where('type', 'Debit')->sum('amount'),
            'last_paid' => ($last_paid = $this->affiliate->transactions->where('type', 'Debit')->sortByDesc('id')->first()) ? (float)$last_paid->amount : null,
            'banking_info' => $this->affiliate->banking_info,
            'banking_info_verification_status' => $this->affiliate->is_banking_info_verified,
            'verification_status' => $this->affiliate->verification_status,
            'suspension_status' => $this->affiliate->is_suspended,
            'fake_referral_counter' => $this->affiliate->fake_referral_counter,
            'last_suspended_date' => $this->affiliate->last_suspended_at,
            'number_of_successful_lead' => $this->affiliate->successful_lead_count,
            'created_at' => $this->affiliate->created_at,
            'updated_at' => $this->affiliate->updated_at
        ]);
    }

    /** @return array */
    public function getForView()
    {
        $this->get();
        $this->format();
        return $this->affiliateReport->toViewArray();
    }

    private function format()
    {
        $this->affiliateReport->name = $this->affiliateReport->name ?: 'N/S';
        $this->affiliateReport->mobile = $this->affiliateReport->mobile ? "`{$this->affiliateReport->mobile}`" : 'N/S';
        $this->affiliateReport->email = $this->affiliateReport->email ?: 'N/S';
        $this->affiliateReport->address = $this->affiliateReport->address ?: 'N/S';
        $this->affiliateReport->gender = $this->affiliateReport->gender ?: 'N/S';
        $this->affiliateReport->is_ambassador = $this->affiliateReport->is_ambassador ? 'Yes' : 'No';
        $this->affiliateReport->has_ambassador = $this->affiliateReport->has_ambassador ? 'Yes' : 'No';
        $this->affiliateReport->ambassador_id = $this->affiliateReport->ambassador_id ?: 'No';
        $this->affiliateReport->ambassador_name = $this->affiliateReport->ambassador_id ? ($this->affiliateReport->ambassador_name ?: 'N/S') : "N/A";
        $this->affiliateReport->ambassador_mobile = $this->affiliateReport->ambassador_id ? ($this->affiliateReport->ambassador_mobile ? "`{$this->affiliateReport->ambassador_mobile}`" : 'N/S') : "N/A";
        $this->affiliateReport->under_ambassador_since = $this->affiliateReport->ambassador_id ? ($this->affiliateReport->under_ambassador_since ? $this->affiliateReport->under_ambassador_since->format('d M Y h:i A') : "N/S") : "N/A";
        $this->affiliateReport->store_name = $this->affiliateReport->store_name ?: 'N/S';
        $this->affiliateReport->location = $this->affiliateReport->location ?: 'N/S';
        $this->affiliateReport->last_paid = $this->affiliateReport->last_paid ?: "N/A";
        $this->affiliateReport->banking_info = $this->affiliateReport->banking_info ? agentBankingInfoImplode($this->affiliateReport->banking_info) : 'N/S';
        $this->affiliateReport->banking_info_verification_status = $this->affiliateReport->banking_info_verification_status ? 'Verified' : 'Unverified';
        $this->affiliateReport->suspension_status = $this->affiliateReport->suspension_status ? 'Suspended' : 'Not Suspended';
        $this->affiliateReport->last_suspended_date = $this->affiliateReport->last_suspended_at ? $this->affiliateReport->last_suspended_at->format('d M Y h:i A') : 'N/S';
        $this->affiliateReport->created_at = $this->affiliateReport->created_at ? $this->affiliateReport->created_at->format('d M Y h:i A') : 'N/S';
        $this->affiliateReport->updated_at = $this->affiliateReport->updated_at ? $this->affiliateReport->updated_at->format('d M Y h:i A') : 'N/S';
    }
}
