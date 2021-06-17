<?php namespace Sheba\PartnerAffiliation;

use App\Models\PartnerAffiliation;
use App\Repositories\NotificationRepository;
use App\Repositories\SmsHandler;
use App\Sheba\Sms\BusinessType;
use App\Sheba\Sms\FeatureType;
use Illuminate\Http\Request;
use Sheba\ModificationFields;

class PartnerAffiliationCreator
{
    use ModificationFields;

    private $notificationRepo;
    private $validator;

    private $data;

    public function __construct(NotificationRepository $notification_repo, PartnerAffiliationCreateValidator $validator)
    {
        $this->notificationRepo = $notification_repo;
        $this->validator = $validator;
    }

    public function setData($data)
    {
        $this->data = $data;
        $this->validator->setData($data);
        return $this;
    }

    public function hasError()
    {
        return $this->validator->hasError();
    }

    public function create()
    {
        $partner_affiliation_data = $this->partnerAffiliationCreateData();
        $partner_affiliation = PartnerAffiliation::create($partner_affiliation_data);

        try {
            $this->sendNotification($partner_affiliation);
            $this->sendSms();
        } catch (\Throwable $e) { }

        return $partner_affiliation;
    }

    private function partnerAffiliationCreateData()
    {
        $this->setModifier($this->data['affiliate']);
        return $this->withBothModificationFields([
            'affiliate_id' => $this->data['affiliate']->id,
            'resource_mobile' => formatMobile($this->data['resource_mobile']),
            'resource_name' => $this->data['resource_name'],
            'company_name' => $this->data['company_name']
        ]);
    }

    private function sendNotification($partner_affiliation)
    {
        $this->notificationRepo->forPartnerAffiliation($partner_affiliation->affiliate, $partner_affiliation);
    }

    private function sendSms()
    {
        $affiliate = $this->data['affiliate']->profile->name ?: $this->data['affiliate']->profile->name;
        (new SmsHandler('partner-affiliation-create'))
            ->setBusinessType(BusinessType::SMANAGER)
            ->setFeatureType(FeatureType::PARTNER_AFFILIATION)
            ->send($this->data['resource_mobile'], [
                'affiliate' => $affiliate
            ]);
    }
}