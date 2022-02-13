<?php namespace Sheba\PosOrderService\Services;


use App\Models\PosOrder;
use App\Sheba\PosOrderService\PosOrderServerClient;
use Sheba\Pos\Order\PosOrderTypes;
use Sheba\Pos\Payment\Creator as PaymentCreator;
use Sheba\Pos\Repositories\PosOrderRepository;

class PaymentService
{
    private $pos_order_id;
    private $partner_id;
    private $pos_order_type;
    private $amount;
    private $method;
    private $method_details;
    private $emi_month;
    private $interest;
    private $is_paid_by_customer;

    /**
     * @param mixed $pos_order_id
     * @return PaymentService
     */
    public function setPosOrderId($pos_order_id)
    {
        $this->pos_order_id = $pos_order_id;
        $posOrder = PosOrder::find($pos_order_id);
        $this->pos_order_type = $posOrder && !$posOrder->is_migrated ? PosOrderTypes::OLD_SYSTEM : PosOrderTypes::NEW_SYSTEM;
        return $this;
    }

    /**
     * @param mixed $partner_id
     * @return PaymentService
     */
    public function setPartnerId($partner_id)
    {
        $this->partner_id = $partner_id;
        return $this;
    }

    /**
     * @param mixed $amount
     * @return PaymentService
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
        return $this;
    }

    /**
     * @param mixed $method
     * @return PaymentService
     */
    public function setMethod($method)
    {
        $this->method = $method;
        return $this;
    }

    /**
     * @param mixed $method_details
     * @return PaymentService
     */
    public function setMethodDetails($method_details)
    {
        $this->method_details = json_encode($method_details);
        return $this;
    }

    /**
     * @param mixed $emi_month
     * @return PaymentService
     */
    public function setEmiMonth($emi_month)
    {
        $this->emi_month = $emi_month;
        return $this;
    }

    /**
     * @param mixed $interest
     * @return PaymentService
     */
    public function setInterest($interest)
    {
        $this->interest = $interest;
        return $this;
    }

    /**
     * @param mixed $is_paid_by_customer
     * @return PaymentService
     */
    public function setIsPaidByCustomer($is_paid_by_customer)
    {
        $this->is_paid_by_customer = $is_paid_by_customer;
        return $this;
    }


    public function onlinePayment()
    {
        $payment_data = [
            'pos_order_id' => $this->pos_order_id,
            'amount' => $this->amount,
            'method' => $this->method,
            'method_details' => $this->method_details,
            'emi_month' => $this->emi_month,
            'interest' => $this->interest,
        ];

        /** @var PaymentCreator $payment_creator */
        $payment_creator = app(PaymentCreator::class);
        $payment_creator->credit($payment_data, $this->pos_order_type);
        if ($this->is_paid_by_customer) {
            if($this->pos_order_type == PosOrderTypes::NEW_SYSTEM) {
                /** @var PosOrderServerClient $client */
                $client = app(PosOrderServerClient::class);
                return $client->put('api/v1/partners/' . $this->partner_id. '/orders/' . $this->pos_order_id, ['interest' => 0, 'bank_transaction_charge' => 0]);
            } else {
                /** @var PosOrderRepository $orderRepo */
                $orderRepo = app(PosOrderRepository::class);
                $order = PosOrder::find($this->pos_order_id);
                return $orderRepo->update($order, ['interest' => 0, 'bank_transaction_charge' => 0]);
            }
        }
    }


}