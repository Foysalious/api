<?php


namespace Sheba\NeoBanking\Banks;


use Illuminate\Contracts\Support\Arrayable;
use Sheba\NeoBanking\Traits\ProtectedGetterTrait;

class BankAccountInfo implements Arrayable
{
    use ProtectedGetterTrait;

    protected $has_account;
    protected $account_no;
    protected $account_status;
    protected $status_message;
    protected $status_message_type;

    /**
     * @return mixed
     */
    public function getHasAccount()
    {
        return $this->has_account;
    }

    /**
     * @return mixed
     */
    public function getAccountNo()
    {
        return $this->account_no;
    }

    /**
     * @return mixed
     */
    public function getAccountStatus()
    {
        return $this->account_status;
    }

    /**
     * @return mixed
     */
    public function getStatusMessage()
    {
        return $this->status_message;
    }

    /**
     * @return mixed
     */
    public function getStatusMessageType()
    {
        return $this->status_message_type;
    }


    /**
     * @param mixed $status_message_type
     * @return BankAccountInfo
     */
    public function setStatusMessageType($status_message_type)
    {
        $this->status_message_type = $status_message_type;
        return $this;
    }

    /**
     * @param mixed $has_account
     * @return BankAccountInfo
     */
    public function setHasAccount($has_account)
    {
        $this->has_account = $has_account;
        return $this;
    }

    /**
     * @param mixed $account_no
     * @return BankAccountInfo
     */
    public function setAccountNo($account_no)
    {
        $this->account_no = $account_no;
        return $this;
    }

    /**
     * @param mixed $status_message
     * @return BankAccountInfo
     */
    public function setStatusMessage($status_message)
    {
        $this->status_message = $status_message;
        return $this;
    }

    /**
     * @param mixed $account_status
     * @return BankAccountInfo
     */
    public function setAccountStatus($account_status)
    {
        $this->account_status = $account_status;
        return $this;
    }
}
