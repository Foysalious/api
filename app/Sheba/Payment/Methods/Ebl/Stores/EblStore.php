<?php


namespace Sheba\Payment\Methods\Ebl\Stores;


abstract class EblStore
{
    protected $baseUrl;
    protected $accessKey;
    protected $profileId;
    protected $merchantID;
    protected $secretKey;
    protected $signedFieldNames = 'access_key,profile_id,transaction_uuid,signed_field_names,unsigned_field_names,signed_date_time,locale,transaction_type,reference_number,amount,currency';
    protected $local            = 'en';

    /**
     * @return mixed
     */
    public function getBaseUrl()
    {
        return $this->baseUrl;
    }

    /**
     * @param mixed $baseUrl
     * @return EblStore
     */
    public function setBaseUrl($baseUrl)
    {
        $this->baseUrl = $baseUrl;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAccessKey()
    {
        return $this->accessKey;
    }

    /**
     * @param mixed $accessKey
     * @return EblStore
     */
    public function setAccessKey($accessKey)
    {
        $this->accessKey = $accessKey;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getProfileId()
    {
        return $this->profileId;
    }

    /**
     * @param mixed $profileId
     * @return EblStore
     */
    public function setProfileId($profileId)
    {
        $this->profileId = $profileId;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getMerchantID()
    {
        return $this->merchantID;
    }

    /**
     * @param mixed $merchantID
     * @return EblStore
     */
    public function setMerchantID($merchantID)
    {
        $this->merchantID = $merchantID;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSecretKey()
    {
        return $this->secretKey;
    }

    /**
     * @param mixed $secretKey
     * @return EblStore
     */
    public function setSecretKey($secretKey)
    {
        $this->secretKey = $secretKey;
        return $this;
    }

    /**
     * @return string
     */
    public function getSignedFieldNames()
    {
        return $this->signedFieldNames;
    }

    /**
     * @param string $signedFieldNames
     * @return EblStore
     */
    public function setSignedFieldNames($signedFieldNames)
    {
        $this->signedFieldNames = $signedFieldNames;
        return $this;
    }

    /**
     * @return string
     */
    public function getLocal()
    {
        return $this->local;
    }

    /**
     * @param string $local
     * @return EblStore
     */
    public function setLocal($local)
    {
        $this->local = $local;
        return $this;
    }

    abstract function getName();
}
