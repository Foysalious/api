<?php namespace Sheba\OrderPlace;

class OrderVoucherData
{
    private $result;

    public function setVoucherRevealData(array $result)
    {
        $this->result = $result;
        return $this;
    }

    public function getOriginalDiscountAmount()
    {
        return (double)$this->result['original_amount'];
    }

    public function getPartnerContribution()
    {
        return (double)$this->result['voucher']['partner_contribution'];
    }

    public function getShebaContribution()
    {
        return (double)$this->result['voucher']['sheba_contribution'];
    }

    public function getVendorContribution()
    {
        return (double)$this->result['voucher']['vendor_contribution'];
    }

    public function getDiscountPercentage()
    {
        return ($this->result['voucher']['is_amount_percentage']) ? (double)$this->result['voucher']['amount'] : 0.00;
    }

    public function getVoucherId()
    {
        return $this->result['id'];
    }

    public function getDiscount()
    {
        return (double)$this->result['amount'];
    }

    /**
     * @return bool
     */
    public function isValid()
    {
        return $this->result && $this->result['is_valid'];
    }
}
