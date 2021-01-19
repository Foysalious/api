<?php namespace Sheba\PaymentLink;

use App\Models\PosCustomer;
use Sheba\EMI\Calculations;
use Sheba\Repositories\Interfaces\PaymentLinkRepositoryInterface;
use Sheba\Repositories\PaymentLinkRepository;
use Sheba\Sms\Sms;
use App\Sheba\Bitly\BitlyLinkShort;

class Creator
{
    private $paymentLinkRepo;
    private $amount;
    private $reason;
    private $userId;
    private $userName;
    private $userType;
    private $isDefault;
    private $status;
    private $linkId;
    private $targetId;
    private $targetType;
    private $data;
    private $paymentLinkCreated;
    private $emiMonth;
    private $payerId;
    private $payerType;
    private $interest;
    private $bankTransactionCharge;
    /** @var BitlyLinkShort */
    private $bitlyLink;


    /**
     * Creator constructor.
     *
     * @param PaymentLinkRepositoryInterface $payment_link_repository
     */
    public function __construct(PaymentLinkRepositoryInterface $payment_link_repository)
    {
        $this->paymentLinkRepo = $payment_link_repository;
        $this->isDefault       = 0;
        $this->amount          = null;
        $this->bitlyLink = new BitlyLinkShort();
    }

    public function setAmount($amount)
    {
        $this->amount = $amount;
        return $this;
    }

    public function setReason($reason)
    {
        $this->reason = $reason;
        return $this;
    }

    public function setUserId($user_id)
    {
        $this->userId = $user_id;
        return $this;
    }

    public function setUserName($user_name)
    {
        $this->userName = (empty($user_name)) ? "UnknownName" : $user_name;
        return $this;
    }

    public function setUserType($user_type)
    {
        $this->userType = $user_type;
        return $this;
    }

    public function setIsDefault($is_default)
    {
        $this->isDefault = $is_default;
        return $this;
    }

    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    public function setPaymentLinkId($link_id)
    {
        $this->linkId = $link_id;
        return $this;
    }

    public function setTargetId($target_id)
    {
        $this->targetId = $target_id;
        return $this;
    }

    public function setTargetType($target_type)
    {
        $this->targetType = $target_type;
        return $this;
    }

    /**
     * @param $emi_month
     * @return $this
     */
    public function setEmiMonth($emi_month)
    {
        $this->emiMonth = $emi_month;
        return $this;
    }

    /**
     * @param mixed $interest
     * @return Creator
     */
    public function setInterest($interest)
    {
        $this->interest = $interest;
        return $this;
    }

    /**
     * @param mixed $bankTransactionCharge
     * @return Creator
     */
    public function setBankTransactionCharge($bankTransactionCharge)
    {
        $this->bankTransactionCharge = $bankTransactionCharge;
        return $this;
    }

    /**
     * @param mixed $payerId
     * @return Creator
     */
    public function setPayerId($payerId)
    {
        $this->payerId = $payerId;
        return $this;
    }

    /**
     * @param mixed $payerType
     * @return Creator
     */
    public function setPayerType($payerType)
    {
        $this->payerType = $payerType;
        return $this;
    }

    /**
     * @method PaymentLinkRepository statusUpdate
     */
    public function editStatus()
    {
        if ($this->status == 'active') {
            $this->status = 1;
        } else {
            $this->status = 0;
        }
        return $this->paymentLinkRepo->statusUpdate($this->linkId, $this->status);
    }

    public function save()
    {
        $this->makeData();
        $this->paymentLinkCreated = $this->paymentLinkRepo->create($this->data);
        return $this->paymentLinkCreated;
    }

    private function makeData()
    {
        $this->data = [
            'amount'                => $this->amount,
            'reason'                => $this->reason,
            'isDefault'             => $this->isDefault,
            'userId'                => $this->userId,
            'userName'              => $this->userName,
            'userType'              => $this->userType,
            'targetId'              => (int)$this->targetId,
            'targetType'            => $this->targetType,
            'payerId'               => $this->payerId,
            'payerType'             => $this->payerType,
            'emiMonth'              => $this->emiMonth,
            'interest'              => $this->interest,
            'bankTransactionCharge' => $this->bankTransactionCharge
        ];
        if ($this->isDefault)
            unset($this->data['reason']);
        if (!$this->targetId)
            unset($this->data['targetId'], $this->data['targetType']);
    }

    public function getPaymentLinkData()
    {
        $payer     = null;
        $payerInfo = $this->getPayerInfo();
        return array_merge([
            'link_id'                 => $this->paymentLinkCreated->linkId,
            'reason'                  => $this->paymentLinkCreated->reason,
            'type'                    => $this->paymentLinkCreated->type,
            'status'                  => $this->paymentLinkCreated->isActive == 1 ? 'active' : 'inactive',
            'amount'                  => $this->paymentLinkCreated->amount,
            'link'                    => $this->paymentLinkCreated->link,
            'emi_month'               => $this->paymentLinkCreated->emiMonth,
            'interest'                => $this->paymentLinkCreated->interest,
            'bank_transaction_charge' => $this->paymentLinkCreated->bankTransactionCharge
        ], $payerInfo);
    }

    public function sentSms()
    {
        if ($this->getPayerInfo()) {
            /** @var PaymentLinkClient $paymentLinkClient */
            $paymentLinkClient = app(PaymentLinkClient::class);
            $paymentLink = $paymentLinkClient->createShortUrl($this->paymentLinkCreated->link);
            $link = null;
            if ($paymentLink) {
                $link = $paymentLink->url->shortUrl;
            }
            $extra_message = $this->targetType == 'pos_order' ? 'করুন। ' : 'করে বাকি পরিশোধ করুন। ';
            $message = 'প্রিয় গ্রাহক, দয়া করে পেমেন্ট লিংকের মাধ্যমে ' . $this->userName . ' কে ' . $this->amount . ' টাকা পে ' . $extra_message . $link . ' Powered by sManager.';
            $mobile = $this->getPayerInfo()['payer']['mobile'];

            /** @var Sms $sms */
            $sms = app(Sms::class);
            $sms = $sms->setVendor('infobip')->to($mobile)->msg($message);
            $sms->shoot();
        }
    }

    private function getPayerInfo()
    {
        $payerInfo = [];
        if ($this->paymentLinkCreated->payerId) {
            try {
                /** @var PosCustomer $payer */
                $payer   = app('App\\Models\\' . pamelCase($this->paymentLinkCreated->payerType))::find($this->paymentLinkCreated->payerId);
                $details = $payer ? $payer->details() : null;
                if ($details) {
                    $payerInfo = [
                        'payer' => [
                            'id'     => $details['id'],
                            'name'   => $details['name'],
                            'mobile' => $details['phone']
                        ]
                    ];
                }
            } catch (\Throwable $e) {
                app('sentry')->captureException($e);
            }
        }
        return $payerInfo;
    }

    public static function validateEmiMonth($data, $type = 'manager')
    {
        if (isset($data['emi_month']) && $data['emi_month'] && (double)$data['amount'] < config('emi.' . $type . '.minimum_emi_amount')) return 'Amount must be greater then or equal BDT ' . config('emi.' . $type . '.minimum_emi_amount');
        return false;
    }

    public function setEmiCalculations()
    {
        if ($this->emiMonth) {
            $data = Calculations::getMonthData($this->amount, $this->emiMonth, false);
            $this->setInterest($data['total_interest'])->setBankTransactionCharge($data['bank_transaction_fee'])->setAmount($data['total_amount']);
        }
        return $this;
    }
}
