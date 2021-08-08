<?php namespace Sheba\Pos\Repositories;

use App\Models\Partner;
use App\Models\PosOrder;
use App\Models\PosOrderPayment;
use App\Sheba\PosOrderService\PosOrderServerClient;
use Sheba\Pos\Order\PosOrderTypes;
use Sheba\Repositories\BaseRepository;

class PosOrderPaymentRepository extends BaseRepository
{
    private $partner;
    private $expenseAccountId;

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
        $client = app(PosOrderServerClient::class);
        return $client->post('api/v1/payments', $data);
    }

    public function deleteFromNewPosOrderSystem($pos_order_id,$amount)
    {
        $data['order_id'] = $pos_order_id;
        $data['amount'] = $amount;
        /** @var PosOrderServerClient $client */
        $client = app(PosOrderServerClient::class);
        $client->post('api/v1/delete-payment', $data);
        return true;
    }

    public function createPosOrderPayment($amount_cleared, $pos_order_id,  $payment_method)
    {
        $payment_data['pos_order_id'] = $pos_order_id;
        $payment_data['amount']       = $amount_cleared;
        $payment_data['method']       = $payment_method;

        /** @var PosOrder $order */
        $order = PosOrder::find($pos_order_id);
        if(isset($order)) {
            $order->calculate();
            if ($order->getDue() > 0) {
                $this->paymentCreator->credit($payment_data);
            }
        }
        if($this->partner->is_migration_completed)
            $this->paymentCreator->credit($payment_data,PosOrderTypes::NEW_SYSTEM);

    }

    /**
     * @param mixed $expenseAccountId
     * @return PosOrderPaymentRepository
     */
    public function setExpenseAccountId($expenseAccountId)
    {
        if($expenseAccountId)
            $this->resolvePartner($expenseAccountId);
        return $this;
    }

    public function resolvePartner($expenseAccountId)
    {
        $this->partner = Partner::where('expense_account_id',$expenseAccountId)->first();
    }

    public function removePosOrderPayment($pos_order_id, $amount){

        if(!$this->partner->is_migration_completed)
            return $this->deleteFromNewPosOrderSystem($pos_order_id,$amount);

        $payment = PosOrderPayment::where('pos_order_id', $pos_order_id)
            ->where('amount', $amount)
            ->where('transaction_type', 'Credit')
            ->first();

        if($payment)
            $payment->delete();
        return true;
    }

}