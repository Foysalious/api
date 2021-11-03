<?php namespace Sheba\Business\CoWorker\Requests;

use App\Models\BusinessMember;

class FinancialRequest
{
    private $businessMember;
    private $tinNumber;
    private $tinCertificate;
    private $bankName;
    private $bankAccountNumber;
    private $bkashNumber;

    /**
     * @param $business_member
     * @return $this
     */
    public function setBusinessMember($business_member)
    {
        $this->businessMember = BusinessMember::findOrFail($business_member);
        return $this;
    }

    /**
     * @return mixed
     */
    public function getBusinessMember()
    {
        return $this->businessMember;
    }

    /**
     * @param $tin_number
     * @return $this
     */
    public function setTinNumber($tin_number)
    {
        $this->tinNumber = $tin_number;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getTinNumber()
    {
        return $this->tinNumber;
    }

    /**
     * @param $tin_certificate
     * @return $this
     */
    public function setTinCertificate($tin_certificate)
    {
        $this->tinCertificate = $tin_certificate;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getTinCertificate()
    {
        return $this->tinCertificate;
    }

    /**
     * @param $bank_name
     * @return $this
     */
    public function setBankName($bank_name)
    {
        $this->bankName = $bank_name;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getBankName()
    {
        return $this->bankName;
    }

    /**
     * @param $bank_account_number
     * @return $this
     */
    public function setBankAccNumber($bank_account_number)
    {
        $this->bankAccountNumber = $bank_account_number;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getBankAccNumber()
    {
        return $this->bankAccountNumber;
    }

    /**
     * @param $bkash_number
     * @return $this
     */
    public function setBkashNumber($bkash_number)
    {
        $this->bkashNumber = $bkash_number;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getBkashNumber()
    {
        return $this->bkashNumber;
    }

    /**
     * @param $data
     * @return bool
     */
    private function isNull($data)
    {
        if ($data == 'null') return true;
        if ($data == null) return true;
        return false;
    }
}