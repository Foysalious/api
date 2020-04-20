<?php namespace Sheba\PaymentLink;

use Sheba\Repositories\Interfaces\PaymentLinkRepositoryInterface;
use Sheba\Repositories\PaymentLinkRepository;

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

    /**
     * Creator constructor.
     * @param PaymentLinkRepositoryInterface $payment_link_repository
     */
    public function __construct(PaymentLinkRepositoryInterface $payment_link_repository)
    {
        $this->paymentLinkRepo = $payment_link_repository;
        $this->isDefault       = 0;
        $this->amount          = null;
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
        $this->userName = $user_name;
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
            'amount'     => $this->amount,
            'reason'     => $this->reason,
            'isDefault'  => $this->isDefault,
            'userId'     => $this->userId,
            'userName'   => $this->userName,
            'userType'   => $this->userType,
            'targetId'   => (int)$this->targetId,
            'targetType' => $this->targetType,
        ];
        if(!is_null($this->emiMonth))
            $this->data['emi_month'] = $this->emiMonth;
        if ($this->isDefault)
            unset($this->data['reason']);
        if (!$this->targetId)
            unset($this->data['targetId'], $this->data['targetType']);
    }

    public function getPaymentLinkData()
    {
        return [
            'link_id' => $this->paymentLinkCreated->linkId,
            'reason'  => $this->paymentLinkCreated->reason,
            'type'    => $this->paymentLinkCreated->type,
            'status'  => $this->paymentLinkCreated->isActive == 1 ? 'active' : 'inactive',
            'amount'  => $this->paymentLinkCreated->amount,
            'link'    => $this->paymentLinkCreated->link,
        ];
    }
}
