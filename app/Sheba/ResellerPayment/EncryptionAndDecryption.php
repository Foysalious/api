<?php

namespace Sheba\ResellerPayment;

class EncryptionAndDecryption
{
    const CypherMethod = "AES-128-CTR";

    private $encryption_key;
    private $iv;
    private $data;

    public function __construct()
    {
        $this->iv = "1234567891011121";
        $this->encryption_key = config('store_configuration.encryption_key');
    }

    /**
     * @param mixed $data
     * @return EncryptionAndDecryption
     */
    public function setData($data): EncryptionAndDecryption
    {
        $this->data = $data;
        return $this;
    }

    public function getEncryptedData()
    {
        return openssl_encrypt($this->data, self::CypherMethod, $this->encryption_key, 0, $this->iv);
    }

    public function getDecryptedData()
    {
        return openssl_decrypt($this->data, self::CypherMethod, $this->encryption_key, 0, $this->iv);
    }

}