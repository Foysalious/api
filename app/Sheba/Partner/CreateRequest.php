<?php namespace Sheba\Partner;

use App\Models\Partner;

class CreateRequest
{
    private $name;
    private $logo;
    private $subDomain;
    private $mobile;
    private $email;
    private $address;
    private $tradeLicense;
    private $tradeLicenseAttachment;
    private $vatRegistrationNumber;
    private $vatRegistrationDocument;
    private $isActiveForB2b;

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     * @return CreateRequest
     */
    public function setName($name)
    {
        $this->name = $name;
        $this->setSubDomain($name);
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSubDomain()
    {
        return $this->subDomain;
    }

    /**
     * @param $name
     * @return CreateRequest
     */
    public function setSubDomain($name)
    {
        $blacklist = ["google", "facebook", "microsoft", "sheba", "sheba.xyz"];

        $is_unicode = (strlen($name) != strlen(utf8_decode($name)));
        if ($is_unicode) $name = "Partner No Name";

        $base_name = $name = preg_replace('/-$/', '', substr(strtolower(clean($name)), 0, 15));
        $already_used = Partner::select('sub_domain')->where('sub_domain', 'like', $name . '%')->pluck('sub_domain')->toArray();
        $counter = 0;
        while (in_array($name, array_merge($blacklist, $already_used))) {
            $name = $base_name . $counter;
            $counter++;
        }

        $this->subDomain = $name;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getMobile()
    {
        return $this->mobile;
    }

    /**
     * @param mixed $mobile
     * @return CreateRequest
     */
    public function setMobile($mobile)
    {
        $this->mobile = $mobile;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param mixed $email
     * @return CreateRequest
     */
    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param mixed $address
     * @return CreateRequest
     */
    public function setAddress($address)
    {
        $this->address = $address;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getIsActiveForB2b()
    {
        return $this->isActiveForB2b;
    }

    /**
     * @param $is_active_for_b2b
     * @return $this
     */
    public function setIsActiveForB2b($is_active_for_b2b)
    {
        $this->isActiveForB2b = $is_active_for_b2b;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getTradeLicense()
    {
        return $this->tradeLicense;
    }

    /**
     * @param $trade_license
     * @return CreateRequest
     */
    public function setTradeLicense($trade_license)
    {
        $this->tradeLicense = $trade_license;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getTradeLicenseAttachment()
    {
        return $this->tradeLicenseAttachment;
    }

    /**
     * @param mixed $trade_license_attachment
     * @return CreateRequest
     */
    public function setTradeLicenseAttachment($trade_license_attachment)
    {
        $this->tradeLicenseAttachment = $trade_license_attachment;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getVatRegistrationNumber()
    {
        return $this->vatRegistrationNumber;
    }

    /**
     * @param mixed $vat_registration_number
     * @return CreateRequest
     */
    public function setVatRegistrationNumber($vat_registration_number)
    {
        $this->vatRegistrationNumber = $vat_registration_number;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getVatRegistrationDocument()
    {
        return $this->vatRegistrationDocument;
    }

    /**
     * @param $vat_registration_document
     * @return $this
     */
    public function setVatRegistrationDocument($vat_registration_document)
    {
        $this->vatRegistrationDocument = $vat_registration_document;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getLogo()
    {
        return $this->logo;
    }

    /**
     * @param mixed $logo
     * @return CreateRequest
     */
    public function setLogo($logo)
    {
        $this->logo = $logo;
        return $this;
    }
}
