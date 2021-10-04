<?php namespace App\Sheba\Payment\Adapters\Payable;

use App\Models\Payable;
use App\Models\PosOrder;
use App\Sheba\Pos\Order\PosOrderObject;
use Carbon\Carbon;
use Sheba\Dal\POSOrder\SalesChannels;
use Sheba\Payment\Adapters\Payable\PayableAdapter;
use Sheba\PaymentLink\PaymentLinkTransformer;

class PaymentLinkOrderAdapter implements PayableAdapter
{
    /** @var PaymentLinkTransformer */
    private $paymentLink;
    private $amount;
    private $payableUser;
    private $description;


    public function setPaymentLink(PaymentLinkTransformer $paymentLinkTransformer)
    {
        $this->paymentLink = $paymentLinkTransformer;
        return $this;
    }

    public function setAmount($amount)
    {
        $this->amount = $amount;
        return $this;
    }

    public function setDescription($purpose)
    {
        $this->description = $purpose;
        return $this;
    }

    /**
     * @param $user
     * @return $this
     */
    public function setPayableUser($user)
    {
        $this->payableUser = $user;
        return $this;
    }

    /**
     * @return Payable
     */
    public function getPayable(): Payable
    {
        $this->resolveDescription();
        $payable = new Payable();
        $payable->type = 'payment_link';
        $payable->type_id = $this->paymentLink->getLinkIdentifier();
        $payable->user_id = $this->payableUser->id;
        $payable->user_type = "App\\Models\\" . class_basename($this->payableUser);
        $payable->amount = $this->getAmount();
        $payable->description = $this->description;
        $payable->completion_type = "payment_link";
        $payable->success_url = $this->resolveSuccessOrFailUrl();
        $payable->fail_url = $this->resolveSuccessOrFailUrl();
        $payable->created_at = Carbon::now();
        $payable->emi_month = $this->paymentLink->getEmiMonth();
        $payable->save();
        return $payable;
    }

    private function getAmount()
    {
        $amount = $this->paymentLink->getAmount();
        return $amount ? (double)$amount : $this->amount;
    }

    private function resolveDescription()
    {
        $reason = $this->paymentLink->getReason();
        $this->description = $reason ? $reason : $this->description;
    }

    public function setModelForPayable($model)
    {
        // TODO: Implement setModelForPayable() method.
    }

    public function setEmiMonth($month)
    {
        // TODO: Implement setEmiMonth() method.
    }

    public function canInit(): bool
    {
        return true;
    }

    private function resolveSuccessOrFailUrl(): string
    {
        $target = $this->paymentLink->getTarget();
        if ($target && $target instanceof PosOrderObject && $target->sales_channel == SalesChannels::WEBSTORE) {
            return ($target->is_migrated ? config('sheba.new_webstore_url') : config('sheba.webstore_url')) . '/' . $target->partner->sub_domain .'/redirect-after-payment/' . $target->id;
        } else {
            return config('sheba.payment_link_web_url') . '/' . $this->paymentLink->getLinkIdentifier() . '/success';
        }
    }
}
