<?php


namespace Sheba\NeoBanking\Banks;

use Sheba\NeoBanking\Traits\ProtectedGetterTrait;

 class BankCompletion
{
    use ProtectedGetterTrait;

    protected $completion;
    protected $can_apply;
    protected $bank_detail_link;
    protected $bank_detail_title;
    protected $message      = '';
    protected $message_type = 'info';

    /**
     * @param mixed $completion
     * @return BankCompletion
     */
    public function setCompletion($completion)
    {
        $this->completion = $completion;
        return $this;
    }

     /**
      * @return mixed
      */
     public function getBankDetailTitle()
     {
         return $this->bank_detail_title;
     }

     /**
      * @param mixed $bank_detail_title
      * @return BankCompletion
      */
     public function setBankDetailTitle($bank_detail_title)
     {
         $this->bank_detail_title = $bank_detail_title;
         return $this;
     }

    /**
     * @param mixed $can_apply
     * @return BankCompletion
     */
    public function setCanApply($can_apply)
    {
        $this->can_apply = $can_apply;
        return $this;
    }

    /**
     * @param mixed $bank_detail_link
     * @return BankCompletion
     */
    public function setBankDetailLink($bank_detail_link)
    {
        $this->bank_detail_link = $bank_detail_link;
        return $this;
    }

    /**
     * @param string $message
     * @return BankCompletion
     */
    public function setMessage($message)
    {
        $this->message = $message;
        return $this;
    }

    /**
     * @param string $message_type
     * @return BankCompletion
     */
    public function setMessageType($message_type)
    {
        $this->message_type = $message_type;
        return $this;
    }

}
