<?php namespace Sheba\MovieTicket;

use App\Models\Voucher;

class MovieTicketRequest
{
    private $name;
    private $mobile;
    private $amount;
    private $email;
    private $trxId;
    private $dtmsId;
    private $ticketId;
    private $confirmStatus;
    private $imageUrl;
    private $voucher;
    private $discount;
    private $discountPercent;
    private $shebaContribution;
    private $vendorContribution;

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param mixed $email
     * @return MovieTicketRequest
     */
    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     * @return MovieTicketRequest
     */
    public function setName($name)
    {
        $this->name = $name;
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
     * @return MovieTicketRequest
     */
    public function setMobile($mobile)
    {
        $this->mobile = $mobile;
        return $this;

    }

    /**
     * @return mixed
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param mixed $amount
     * @return MovieTicketRequest
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getTrxId()
    {
        return $this->trxId;
    }

    /**
     * @param mixed $trxId
     * @return MovieTicketRequest
     */
    public function setTrxId($trxId)
    {
        $this->trxId = $trxId;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDtmsId()
    {
        return $this->dtmsId;
    }

    /**
     * @param mixed $dtmsId
     * @return MovieTicketRequest
     */
    public function setDtmsId($dtmsId)
    {
        $this->dtmsId = $dtmsId;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getTicketId()
    {
        return $this->ticketId;
    }

    /**
     * @param mixed $ticketId
     * @return MovieTicketRequest
     */
    public function setTicketId($ticketId)
    {
        $this->ticketId = $ticketId;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getConfirmStatus()
    {
        return $this->confirmStatus;
    }

    /**
     * @param mixed $confirmStatus
     * @return MovieTicketRequest
     */
    public function setConfirmStatus($confirmStatus)
    {
        $this->confirmStatus = $confirmStatus;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getImageUrl()
    {
        return $this->imageUrl;
    }

    /**
     * @param mixed $imageUrl
     * @return MovieTicketRequest
     */
    public function setImageUrl($imageUrl)
    {
        $this->imageUrl = $imageUrl;
        return $this;
    }

    /**
     * @param $voucher
     * @return $this
     */
    public function setVoucher($voucher)
    {
        $this->voucher = ($voucher instanceof Voucher) ? $voucher : Voucher::find($voucher);
        if ($this->voucher) {
            $amount = $this->getDiscountAmount();
            $discount_percent = $this->voucher->is_amount_percentage ? $this->voucher->amount : 0.00;
            $this->setDiscount($amount);
            $this->setDiscountPercent($discount_percent);
            $this->setShebaContribution($this->voucher->sheba_contribution);
            $this->setVendorContribution($this->voucher->partner_contribution);
        }

        return $this;
    }

    public function getVoucher()
    {
        return $this->voucher;
    }

    public function setDiscount($discount)
    {
        $this->discount = $discount;
        return $this;
    }

    public function getDiscount()
    {
        return $this->discount?:0;
    }

    public function setDiscountPercent($discount_percent)
    {
        $this->discountPercent = $discount_percent;
        return $this;
    }

    public function getDiscountPercent()
    {
        return $this->discountPercent;
    }
    
    public function setShebaContribution($sheba_contribution)
    {
        $this->shebaContribution = $sheba_contribution;
        return $this;
    }
    
    public function setVendorContribution($vendor_contribution)
    {
        $this->vendorContribution = $vendor_contribution;
        return $this;
    }

    public function getShebaContribution()
    {
        return $this->shebaContribution;
    }

    public function getVendorContribution()
    {
        return $this->vendorContribution;
    }

    public function getDiscountAmount()
    {
        if ($this->voucher->is_amount_percentage) {
            $amount = ((double)$this->amount * $this->voucher->amount) / 100;
            if ($this->voucher->cap != 0 && $amount > $this->voucher->cap) {
                $amount = $this->voucher->cap;
            }
            return $amount;
        } else {
            return $this->validateDiscountValue($this->amount, $this->voucher['amount']);
        }
    }

    private function validateDiscountValue($amount, $discount_value)
    {
        return $amount < $discount_value ? $amount : $discount_value;
    }
}
