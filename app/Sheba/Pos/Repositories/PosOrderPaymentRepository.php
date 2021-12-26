<?php namespace Sheba\Pos\Repositories;

use App\Models\Partner;
use App\Models\PosOrder;
use App\Models\PosOrderPayment;
use App\Sheba\PosOrderService\PosOrderServerClient;
use App\Sheba\UserMigration\Modules;
use Sheba\PosOrderService\Services\PaymentService;
use Sheba\Repositories\BaseRepository;

class PosOrderPaymentRepository extends BaseRepository
{
    private $partner;
    private $method_details;

    /**
     * @param array $data
     * @return PosOrderPayment
     */
    public function save(array $data)
    {
        return PosOrderPayment::create($this->withCreateModificationField($data));
    }

    public function saveToNewPosOrderSystem($data)
    {
        $payment_data = [
            'pos_order_id' => $data['pos_order_id'] ?? null,
            'amount' => $data['amount'] ?? null,
            'payment_method' => $data['method'] ?? null,
            'method_details' => $data['method_details'] ?? null,
            'emi_month' => $data['emi_month'] ?? null,
            'interest' => $data['interest'] ?? null,
            'transaction_type' => $data['transaction_type'] ?? null,
        ];
        $client = app(PosOrderServerClient::class);
        return $client->post('api/v1/payments', $payment_data);
    }

    public function deleteFromNewPosOrderSystem($pos_order_id,$amount)
    {
        $data['order_id'] = $pos_order_id;
        $data['amount'] = $amount;
        /** @var PosOrderServerClient $client */
        $client = app(PosOrderServerClient::class);
        $client->post('api/v1/payment/delete', $data);
        return true;
    }

    public function createPosOrderPayment($amount_cleared, $pos_order_id,  $payment_method)
    {
        $payment_data['pos_order_id'] = $pos_order_id;
        $payment_data['amount']       = $amount_cleared;
        $payment_data['method']       = $payment_method;
        $payment_data['transaction_type'] = 'Credit';
        $payment_data['method_details'] = $this->method_details;

        /** @var PosOrder $order */
        $order = PosOrder::find($pos_order_id);
        if(isset($order) && !$order->partner->isMigrated(Modules::POS)) {
            $order->calculate();
            if ($order->getDue() > 0) {
                return $this->save($payment_data);
            }
            return false;
        }
        return $this->saveToNewPosOrderSystem($payment_data);
    }

    public function setMethodDetails($method_details)
    {
        $this->method_details = json_encode($method_details);
        return $this;
    }


    public function resolvePartner($expenseAccountId)
    {
        $this->partner = Partner::where('expense_account_id',$expenseAccountId)->first();
    }

    public function removePosOrderPayment($pos_order_id, $amount){

        $payment = PosOrderPayment::where('pos_order_id', $pos_order_id)
            ->where('amount', $amount)
            ->where('transaction_type', 'Credit')
            ->first();

        if($payment)
        {
            $payment->delete();
            return true;
        }

        return $this->deleteFromNewPosOrderSystem($pos_order_id,$amount);
    }

}