<?php namespace Sheba\Partner;


use App\Models\Partner;
use Sheba\Repositories\PartnerRepository;

class Updater
{
    /** @var Partner */
    protected $partner;
    protected $address;
    protected $data;
    /** @var PartnerRepository */
    protected $partnerRepository;
    protected $isWebstoreSmsActive;

    public function __construct(PartnerRepository $partnerRepository)
    {
        $this->partnerRepository = $partnerRepository;
    }

    public function setPartner(Partner $partner)
    {
        $this->partner = $partner;
        return $this;
    }

    public function setAddress($address)
    {
        $this->address = $address;
        return $this;
    }

    public function setIsWebstoreSmsActive($isWebstoreSmsActive)
    {
        $this->isWebstoreSmsActive = $isWebstoreSmsActive;
        return $this;
    }

    public function update()
    {
        $data = $this->makeData();
        $this->partnerRepository->update($this->partner, $data);
    }

    private function makeData()
    {
        if (isset($this->address)) $this->data['address'] = $this->address;
        if (isset($this->isWebstoreSmsActive)) $this->data['is_webstore_sms_active'] = $this->isWebstoreSmsActive;
        return $this->data;
    }
}