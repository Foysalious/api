<?php namespace Sheba\MovieTicket;

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
    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param mixed $email
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
     */
    public function setImageUrl($imageUrl)
    {
        $this->imageUrl = $imageUrl;
    }

    /**
     * @return mixed
     */
}