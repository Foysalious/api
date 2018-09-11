<?php


namespace Sheba\PayCharge\Adapters\PayCharged;


use App\Sheba\PayCharge\Rechargable;
use Sheba\PayCharge\PayCharged;

class RechargeAdapter implements PayChargedAdapter
{
    private $user;
    private $transactionId;

    public function __construct(Rechargable $user, $transaction_id)
    {
        $this->user = $user;
        $this->transactionId = $transaction_id;
    }

    public function getPayCharged(): PayCharged
    {
        $pay_chargable = new PayCharged();
        $pay_chargable->type = 'recharge';
        $pay_chargable->user = $this->user;
        $pay_chargable->userId = $this->user->id;
        $pay_chargable->userType = "App\\Models\\" . class_basename($this->user);
        $pay_chargable->transactionId = $this->transactionId;
        return $pay_chargable;
    }
}