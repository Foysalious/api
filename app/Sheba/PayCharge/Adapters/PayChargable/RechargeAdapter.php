<?php

namespace Sheba\PayCharge\Adapters\PayChargable;


use App\Sheba\PayCharge\Rechargable;
use Sheba\PayCharge\PayChargable;

class RechargeAdapter implements PayChargableAdapter
{
    private $user;
    private $amount;

    public function __construct(Rechargable $user, $amount)
    {
        $this->user = $user;
        $this->amount = $amount;
    }

    public function getPayable(): PayChargable
    {
        $pay_chargable = new PayChargable();
        $pay_chargable->type = 'recharge';
        $pay_chargable->amount = (double)$this->amount;
        $pay_chargable->completionClass = 'RechargeComplete';
        $pay_chargable->redirectUrl = env('SHEBA_FRONT_END_URL') . '/profile/wallet';
        $pay_chargable->userId = $this->user->id;
        $pay_chargable->userType = "App\\Models\\" . class_basename($this->user);

        return $pay_chargable;
    }
}