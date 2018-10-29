<?php

namespace Sheba\Payment\Adapters\Payable;


use App\Models\Payable;
use App\Sheba\Payment\Rechargable;
use Carbon\Carbon;

class RechargeAdapter implements PayableAdapter
{
    private $user;
    private $amount;

    public function __construct(Rechargable $user, $amount)
    {
        $this->user = $user;
        $this->amount = $amount;
    }

    public function getPayable(): Payable
    {
        $payable = new Payable();
        $payable->type = 'wallet_recharge';
        $payable->user_id = $this->user->id;
        $payable->user_type = "App\\Models\\Customer";
        $payable->amount = (double)$this->amount;
        $payable->completion_type = 'wallet_recharge';
        $payable->success_url = config('sheba.front_url') . '/profile/credit';
        $payable->created_at = Carbon::now();
        $payable->save();
        return $payable;
    }
}