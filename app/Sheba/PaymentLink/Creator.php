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
    private $target;
    private $data;

    /**
     * Creator constructor.
     * @param PaymentLinkRepositoryInterface $payment_link_repository
     */
    public function __construct(PaymentLinkRepositoryInterface $payment_link_repository)
    {
        $this->paymentLinkRepo = $payment_link_repository;
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

    public function setTarget($target)
    {
        $this->target = $target;
        return $this;
    }

    /**
     * @method PaymentLinkRepository statusUpdate
     */
    public function editStatus()
    {
        if ($this->status == 'active') $this->status = 1;
        $this->status = 0;
        return $this->paymentLinkRepo->statusUpdate($this->linkId, $this->status);
    }

    /**
     * @method PaymentLinkRepository create
     */

    public function save()
    {
        $this->makeData();
        return $this->paymentLinkRepo->create($this->data);
    }

    private function makeData()
    {
        $this->data = [
            'amount' => $this->isDefault ? 0 : $this->amount,
            'reason' => $this->reason,
            'isDefault' => $this->isDefault ? $this->isDefault : 0,
            'userId' => $this->userId,
            'userName' => $this->userName,
            'userType' => $this->userType,
            'targetId' => (int)$this->targetId,
            'target' => $this->target,
        ];
        if ($this->isDefault) unset($this->data['reason']);
        if (!$this->targetId) unset($this->data['targetId'], $this->data['target']);
    }
}
