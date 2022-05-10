<?php namespace App\Sheba\Partner;

use Sheba\Dal\PartnerPackageFeatureCounter\EloquentImplementation as PartnerPackageFeatureCounter;
use Sheba\ModificationFields;

class PackageFeatureCount
{
    use ModificationFields;

    protected $partnerPackageFeatureCounter;

    public function __construct(PartnerPackageFeatureCounter $partnerPackageFeatureCounter)
    {
        $this->partnerPackageFeatureCounter = $partnerPackageFeatureCounter;
    }

    public function topupCurrentCount($partner): int
    {
        return $this->featuresCount($partner)->topup;
    }

    public function smsCurrentCount($partner): int
    {
        return $this->featuresCount($partner)->sms;
    }

    public function deliveryCurrentCount($partner): int
    {
        return $this->featuresCount($partner)->delivery;
    }

    public function smsCountUpdate($updated_count, $partner)
    {
        $features_count = $this->featuresCount($partner);
        $data['sms'] = $updated_count;
        $features_count->update($this->withUpdateModificationField($data));
    }

    public function topupCountUpdate($updated_count, $partner)
    {
        $features_count = $this->featuresCount($partner);
        $data['topup'] = $updated_count;
        $features_count->update($this->withUpdateModificationField($data));
    }

    public function deliveryCountUpdate($updated_count, $partner)
    {
        $features_count = $this->featuresCount($partner);
        $data['delivery'] = $updated_count;
        $features_count->update($this->withUpdateModificationField($data));
    }

    private function featuresCount($partner)
    {
        return $this->partnerPackageFeatureCounter->where('partner_id', $partner)->first();
    }
}