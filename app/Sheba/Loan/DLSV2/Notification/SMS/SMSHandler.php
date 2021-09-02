<?php namespace App\Sheba\Loan\DLSV2\Notification\SMS;

use App\Models\User;
use Carbon\Carbon;
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
    private $user;
    private $sms;

    public function __construct()
    {
        /** @var Sms sms */
        $this->sms = app(Sms::class);
    }


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
     * @param $user
     * @return SMSHandler
     */
    public function setUser($user)
    {
        $this->user = $user;
        return $this;
    }

    public function setFeatureType($featureType)
    {
        $this->sms->setFeatureType($featureType);
        return $this;
    }

    public function setBusinessType($businessType)
    {
        $this->sms->setBusinessType($businessType);
        return $this;
    }
    /**
     * @return IPDCSmsLogModel
     */
    public function shoot()
    {
        $this->cost = $this->sms->msg($this->message)->getCost();
        $this->sms->msg($this->message)->to($this->mobile)->shoot();
        return $this->shootLog();
    }

    /**
     * @return IPDCSmsLogModel
     */
    private function shootLog()
    {
        $this->makeLogData();
        $this->ipdcSmsLog = new IPDCSmsLogModel();
        return $this->ipdcSmsLog->create($this->ipdcSmsLogData);
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
            'used_on_id' => $this->loanId,
            'created_at' => Carbon::now(),
            'created_by' => $this->user->id,
            'created_by_name' => ($this->user instanceof User) ? $this->user->name : $this->user->profile->name
        ];
    }


}