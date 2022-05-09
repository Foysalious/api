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

    public function topupCurrentCount(): int
    {
        return 10;
    }

    public function smsCurrentCount($partner): int
    {
        $features_count = $this->featuresCount($partner);
        return $features_count->sms;
    }

    public function deliveryCurrentCount(): int
    {
        return 20;
    }

    public function smsCountUpdate($updated_count, $partner)
    {
        $features_count = $this->featuresCount($partner);
        $data['sms'] = $updated_count;
        $features_count->update($this->withUpdateModificationField($data));
    }

    private function featuresCount($partner)
    {
        return $this->partnerPackageFeatureCounter->where('partner_id', $partner)->first();
    }
}