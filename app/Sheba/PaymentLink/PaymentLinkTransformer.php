<?php namespace Sheba\PaymentLink;

use App\Models\Partner;
use App\Models\PosCustomer;
use Sheba\Transactions\Wallet\HasWalletTransaction;
use Sheba\Dal\ExternalPayment\Model as ExternalPayment;
use stdClass;

class PaymentLinkTransformer
{
    private $response;
    private $target;

    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @param stdClass $response
     * @return $this
     */
    public function setResponse(stdClass $response)
    {
        $this->response = $response;
        return $this;
    }

    public function getLinkID()
    {
        return $this->response->linkId;
    }

    public function getReason()
    {
        return $this->response->reason;
    }

    public function getLink()
    {
        return $this->response->link;
    }

    public function getLinkIdentifier()
    {
        return $this->response->linkIdentifier;
    }

    public function getAmount()
    {
        return $this->response->amount;
    }

    public function getIsActive()
    {
        return $this->response->isActive;
    }

    public function getIsDefault()
    {
        return $this->response->isDefault;
    }

    public function getEmiMonth()
    {
        return isset($this->response->emiMonth) ? $this->response->emiMonth : null;
    }

    public function isEmi()
    {
        return !is_null($this->getEmiMonth());
    }

    public function getInterest()
    {
        return isset($this->response->interest) ? $this->response->interest : null;
    }

    public function getBankTransactionCharge()
    {
        return isset($this->response->bankTransactionCharge) ? $this->response->bankTransactionCharge : null;
    }

    /**
     * @return HasWalletTransaction
     */
    public function getPaymentReceiver()
    {
        $model_name = "App\\Models\\" . ucfirst($this->response->userType);
        return $model_name::find($this->response->userId);
    }

    /**
     * @return null
     */
    public function getPayer()
    {
        $order = $this->getTarget();
        if ($order && $order instanceof ExternalPayment) return $this->getPaymentLinkPayer();
        return $order ? $order->customer->profile : $this->getPaymentLinkPayer();
    }

    /**
     * @return mixed
     */
    public function getTarget()
    {
        if ($this->response->targetType) {
            $model_name   = $this->resolveTargetClass();
            $this->target = $model_name::find($this->response->targetId);
            return $this->target;
        } else
            return null;
    }

    private function resolveTargetClass()
    {
        $model_name = "App\\Models\\";
        if ($this->response->targetType == 'pos_order')
            return $model_name . 'PosOrder';
        if ($this->response->targetType == 'external_payment')
            return "Sheba\\Dal\\ExternalPayment\\Model";
    }

    private function getPaymentLinkPayer()
    {
        $model_name = "App\\Models\\";
        if (isset($this->response->payerId)) {
            $model_name = $model_name . pamelCase($this->response->payerType);
            /** @var PosCustomer $customer */
            $customer = $model_name::find($this->response->payerId);
            return $customer ? $customer->profile : null;
        }
    }

    public function isForMissionSaveBangladesh()
    {
        $receiver = $this->getPaymentReceiver();
        if ($receiver instanceof Partner) return false;
        /** @var Partner $receiver */
        return $receiver->isMissionSaveBangladesh();
    }

    public function isExternalPayment()
    {
        return !!($this->target instanceof ExternalPayment);
    }

    public function getSuccessUrl()
    {
        return $this->target->success_url.'?transaction_id='.$this->target->transaction_id;
    }

    public function getFailUrl()
    {
        return $this->target->fail_url.'?transaction_id='.$this->target->transaction_id;
    }

    public function toArray()
    {
        $user       = $this->getPaymentReceiver();
        $payer      = $this->getPayer();
        $isExternal = $this->isExternalPayment();
        return [
                   'id'                  => $this->getLinkID(),
                   'identifier'          => $this->getLinkIdentifier(),
                   'purpose'             => $this->getReason(),
                   'amount'              => $this->getAmount(),
                   'emi_month'           => $this->getEmiMonth(),
                   'payment_receiver'    => [
                       'name'  => $user->name,
                       'image' => $user->logo,
                       'id'    => $user->id,
                   ],
                   'payer'               => $payer ? [
                       'name'   => $payer->name,
                       'mobile' => $payer->mobile
                   ] : null,
                   'is_external_payment' => $isExternal,
               ] + ($isExternal ? ['success_url' => $this->getSuccessUrl(), 'fail_url' => $this->getFailUrl()] : []);

    }
}
