<?php namespace Sheba\Partner\Webstore;


use App\Exceptions\DoNotReportException;
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
     * @param $sub_domain
     * @return WebstoreSettingsUpdateRequest
     * @throws DoNotReportException
     */
    public function setSubDomain($sub_domain)
    {
        if (is_numeric($sub_domain)) throw new DoNotReportException('দুঃখিত, স্টোর লিংকে শুধুমাত্র নাম্বার ব্যবহার করা যাবে না !', 400);
        $sub_domain = $this->removeRestrictedCharacters(strtolower($sub_domain));
        if ($this->subDomainAlreadyExist($sub_domain)) throw new DoNotReportException('এই লিংক-টি ইতোমধ্যে ব্যবহৃত হয়েছে!', 400);
        $this->subDomain = $sub_domain;
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
        if (isset($this->deliveryCharge)) $data['delivery_charge'] = (double)$this->deliveryCharge;
        if (isset($this->hasWebstore)) $data['has_webstore'] = $this->hasWebstore;
        return $data;
    }

    public function update()
    {
        $data = $this->makeData();
        $repo = new PartnerRepository($this->partner);
        $repo->updateWebstoreSettings($data);
    }

    private function removeRestrictedCharacters($sub_domain)
    {
        return str_replace(['/', '$', '#', ' ', '?', '%'], '', $sub_domain);
    }

    private function subDomainAlreadyExist($sub_domain)
    {
        if (Partner::where('sub_domain', $sub_domain)->exists()) return true;
        return false;
    }
}