<?php namespace App\Sheba\Loan\DLSV2\Notification\SMS;

use Sheba\Dal\IPDCSmsLog\Model as IPDCSmsLogModel;
use Sheba\ModificationFields;
use Sheba\Sms\Sms;

class SMSHandler
{
    use ModificationFields;

    private $message;
    private $mobile;
    private $cost;
    private $ipdcSmsLog;
    private $ipdcSmsLogData;
    private $msgType;
    private $loanId;


    /**
     * @param $message
     * @return $this
     */
    public function setMsg($message)
    {
        $this->message = $message;
        return $this;
    }

    /**
     * @param $msg_type
     * @return $this
     */
    public function setMsgType($msg_type)
    {
        $this->msgType = $msg_type;
        return $this;
    }

    /**
     * @param $mobile
     * @return $this
     */
    public function setMobile($mobile)
    {
        $this->mobile = $mobile;
        return $this;
    }

    /**
     * @param $lona_id
     * @return $this
     */
    public function setLoanId($lona_id)
    {
        $this->loanId = $lona_id;
        return $this;
    }

    /**
     * @return bool
     */
    public function shoot()
    {
        $this->cost = (new Sms())->msg($this->message)->getCost();
        (new Sms())->msg($this->message)->to($this->mobile)->shoot();
        return $this->shootLog();
    }

    /**
     * @return bool
     */
    private function shootLog()
    {
        $this->makeLogData();
        $this->ipdcSmsLog = new IPDCSmsLogModel($this->withCreateModificationField($this->ipdcSmsLogData));
        return $this->ipdcSmsLog->save();
    }

    /**
     *
     */
    private function makeLogData()
    {
        $this->ipdcSmsLogData = [
            'mobile' => $this->mobile,
            'cost'  => $this->cost,
            'content' => $this->message,
            'log' => $this->msgType,
            'used_on_type' => "App\\Models\\PartnerBankLoan",
            'used_on_id' => $this->loanId

        ];
    }


}