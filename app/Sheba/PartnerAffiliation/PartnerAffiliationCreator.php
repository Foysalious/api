<?php namespace Sheba\PartnerAffiliation;

use App\Models\PartnerAffiliation;
use App\Repositories\NotificationRepository;
use App\Repositories\SmsHandler;
use Sheba\ModificationFields;

class PartnerAffiliationCreator
{
    use ModificationFields;

    public function create($data)
    {
        $partner_affiliation_data = $this->partnerAffiliationCreateData($data);
        $partner_affiliation = PartnerAffiliation::create($partner_affiliation_data);
        $this->sendNotification($partner_affiliation);
        $this->sendSms($data);
        return $partner_affiliation;
    }

    private function partnerAffiliationCreateData($data)
    {
        $this->setModifier($data['affiliate']);
        return $this->withBothModificationFields([
            'affiliate_id' => $data['affiliate']->id,
            'resource_mobile' => formatMobile($data['resource_mobile']),
            'resource_name' => $data['resource_name'],
            'company_name' => $data['company_name']
        ]);
    }

    private function sendNotification($partner_affiliation)
    {
        try {
            (new NotificationRepository())->forPartnerAffiliation($partner_affiliation->affiliate, $partner_affiliation);
        } catch (\Throwable $e) {

        }
    }

    private function sendSms($data)
    {
        try {
            $affiliate = $data['affiliate']->profile->name ?: $data['affiliate']->profile->name;
            (new SmsHandler('partner-affiliation-create'))->send($data['resource_mobile'], [
                'affiliate' => $affiliate
            ]);
        } catch (\Throwable $e) {

        }

    }
}