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
    protected $hasWebstore;
    protected $address;

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
     * @param $hasWebstore
     * @return WebstoreSettingsUpdateRequest
     */
    public function setHasWebstore($hasWebstore)
    {
        $this->hasWebstore = $hasWebstore;
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
        $this->subDomain = str_replace(' ', '', $subDomain);
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

    /**
     * @param $address
     * @return WebstoreSettingsUpdateRequest
     */
    public function setAddress($address)
    {
        $this->address = $address;
        return $this;
    }

    private function makeData()
    {
        $data = [];
        if (isset($this->isWebstorePublished)) $data['is_webstore_published'] = $this->isWebstorePublished;
        if (isset($this->name)) $data['name'] = $this->name;
        if (isset($this->subDomain)) $data['sub_domain'] = $this->subDomain;
        if (isset($this->deliveryCharge)) $data['delivery_charge'] = (double) $this->deliveryCharge;
        if (isset($this->hasWebstore)) $data['has_webstore'] = $this->hasWebstore;
        return $data;
    }

    public function update()
    {
        $data = $this->makeData();
        $repo = new PartnerRepository($this->partner);
        $repo->updateWebstoreSettings($data);

    }

    public function toggleSmsActivation()
    {
        $repo = new PartnerRepository($this->partner);
        $repo->toggleSmsActivation();
    }

    public function updateAddress()
    {
        $repo = new PartnerRepository($this->partner);
        $repo->updateAddress($this->address);
    }

}