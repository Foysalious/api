<?php namespace App\Sheba\Payment\Adapters\Payable;


use App\Models\Payable;
use Carbon\Carbon;
use Sheba\Repositories\PaymentLinkRepository;

class PaymentLinkOrderAdapter
{
    private $paymentLink, $amount, $userType, $user;

    public function setPaymentLink($identifier, $amount = null)
    {
        $this->paymentLink = $this->get($identifier);
        if ($this->paymentLink['amount']) {
            $this->amount = $this->paymentLink['amount'];
        } else {
            $this->amount = $amount;
        }
        return $this;
    }

    public function setUser($user)
    {
        $this->user = $user;
        return $this;
    }

    public function setUserType($type)
    {
        $this->userType = $type;
        return $this;
    }

    public function getPayable(): Payable
    {
        $payable = new Payable();
        $payable->type = 'payment_link';
        $payable->type_id = $this->paymentLink['linkId'];
        $payable->user_id = $this->user->id;
        $payable->user_type = $this->userType;
        $payable->amount = $this->amount;
        $payable->completion_type = "payment_link";
        $payable->success_url = config('sheba.front_url') . '/profile/payments-links/' . $this->paymentLink['linkId'];
        $payable->created_at = Carbon::now();
        $payable->save();
        return $payable;
    }

    private function get($identifier)
    {
        $type = strtolower(class_basename($this->userType));
        return (new PaymentLinkRepository())->getPaymentLinkDetails($this->user->id, $type, $identifier);

    }
}
