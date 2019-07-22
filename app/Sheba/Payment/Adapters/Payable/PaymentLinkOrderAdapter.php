<?php namespace App\Sheba\Payment\Adapters\Payable;


use App\Models\Payable;
use App\Sheba\Payment\Exceptions\PaymentAmountNotSet;
use Carbon\Carbon;
use Sheba\Repositories\PaymentLinkRepository;

class PaymentLinkOrderAdapter
{
    private $paymentLink, $amount, $userType, $user;

    /**
     * @param $identifier
     * @param null $amount
     * @return $this
     * @throws PaymentAmountNotSet
     * @throws \App\Sheba\Payment\Exceptions\PayableNotFound
     */
    public function setPaymentLink($identifier, $amount = null)
    {
        $this->paymentLink = $this->get($identifier);
        if (!empty($this->paymentLink['amount'])) {
            $this->amount = $this->paymentLink['amount'];
        } else {
            $this->amount = $amount;
        }
        if (empty($this->amount)) throw new PaymentAmountNotSet();
        return $this;
    }

    /**
     * @param $user
     * @return $this
     */
    public function setUser($user)
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @param $type
     * @return $this
     */
    public function setUserType($type)
    {
        $this->userType = $type;
        return $this;
    }

    /**
     * @return Payable
     */
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

    /**
     * @param $identifier
     * @return mixed
     * @throws \App\Sheba\Payment\Exceptions\PayableNotFound
     */
    private function get($identifier)
    {
        $type = strtolower(class_basename($this->userType));
        return (new PaymentLinkRepository())->getPaymentLinkDetails($this->user->id, $type, $identifier);

    }
}
