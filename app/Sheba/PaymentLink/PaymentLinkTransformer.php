<?php namespace Sheba\PaymentLink;

use App\Models\PosCustomer;
use Sheba\Transactions\Wallet\HasWalletTransaction;
use stdClass;

class PaymentLinkTransformer {
    private $response;

    public function getResponse() {
        return $this->response;
    }

    /**
     * @param stdClass $response
     * @return $this
     */
    public function setResponse(stdClass $response) {
        $this->response = $response;
        return $this;
    }

    public function getLinkID() {
        return $this->response->linkId;
    }


    public function getReason() {
        return $this->response->reason;
    }

    public function getLink() {
        return $this->response->link;
    }

    public function getLinkIdentifier() {
        return $this->response->linkIdentifier;
    }


    public function getAmount() {
        return $this->response->amount;
    }

    public function getIsActive() {
        return $this->response->isActive;
    }

    public function getIsDefault() {
        return $this->response->isDefault;
    }

    /**
     * @return HasWalletTransaction
     */
    public function getPaymentReceiver() {
        $model_name = "App\\Models\\" . ucfirst($this->response->userType);
        return $model_name::find($this->response->userId);
    }

    /**
     * @return null
     */
    public function getPayer() {
        $order = $this->getTarget();
        return $order ? $order->customer->profile : $this->getPaymentLinkPayer();
    }

    /**
     * @return mixed
     */
    public function getTarget() {
        if ($this->response->targetType) {
            $model_name = $this->resolveTargetClass();
            return $model_name::find($this->response->targetId);
        } else
            return null;
    }

    private function resolveTargetClass() {
        $model_name = "App\\Models\\";
        if ($this->response->targetType == 'pos_order')
            return $model_name . 'PosOrder';
    }

    private function getPaymentLinkPayer() {
        $model_name = "App\\Models\\";
        if (isset($this->response->payerId)) {
            $model_name = $model_name . pamelCase($this->response->payerType);
            /** @var PosCustomer $customer */
            $customer = $model_name::find($this->response->payerId);
            return $customer ? $customer->profile : null;
        }
    }
}
