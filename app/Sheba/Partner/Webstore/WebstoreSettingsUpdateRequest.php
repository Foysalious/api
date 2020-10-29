<?php namespace Sheba\Partner\Webstore;


use App\Models\Partner;
use App\Repositories\PartnerRepository;

class WebstoreSettingsUpdateRequest
{
    protected $isWebstorePublished;
    protected $name;
    protected $subDomain;
    protected $logo;
    protected $deliveryCharge;
    /** @var PartnerRepository */
    protected $partnerRepository;
    /** @var Partner */
    protected $partner;
    protected $data;

    /**
     * @param Partner $partner
     * @return WebstoreSettingsUpdateRequest
     */
    public function setPartner($partner)
    {
        $this->partner = $partner;
        return $this;
    }

    /**
     * @param $isWebstorePublished
     * @return WebstoreSettingsUpdateRequest
     */
    public function setIsWebstorePublished($isWebstorePublished)
    {
        $this->isWebstorePublished = $isWebstorePublished;
        return $this;
    }

    /**
     * @param $name
     * @return WebstoreSettingsUpdateRequest
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @param $subDomain
     * @return WebstoreSettingsUpdateRequest
     */
    public function setSubDomain($subDomain)
    {
        $this->subDomain = $subDomain;
        return $this;
    }

    /**
     * @param $deliveryCharge
     * @return WebstoreSettingsUpdateRequest
     */
    public function setDeliveryCharge($deliveryCharge)
    {
        $this->deliveryCharge = $deliveryCharge;
        return $this;
    }

    private function makeData()
    {
        $data = [];
        if ($this->isWebstorePublished) $data['is_webstore_published'] = $this->isWebstorePublished;
        if ($this->name) $data['name'] = $this->name;
        if ($this->subDomain) $data['sub_domain'] = $this->subDomain;
        if ($this->deliveryCharge) $data['delivery_charge'] = (double) $this->deliveryCharge;
        return $data;
    }

    public function update()
    {
        $data = $this->makeData();
        $repo = new PartnerRepository($this->partner);
        $repo->updateWebstoreSettings($data);

    }

}