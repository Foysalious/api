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

    public function getShebaContribution()
    {
        return (double)$this->result['voucher']['sheba_contribution'];
    }

    public function getPartnerContribution()
    {
        return (double)$this->result['voucher']['partner_contribution'];
    }


    public function getDiscountPercentage()
    {
        return (double)$this->result['voucher']['amount'];
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