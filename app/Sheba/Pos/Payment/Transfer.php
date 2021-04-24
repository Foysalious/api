<?php namespace Sheba\Pos\Payment;

use App\Models\PosOrder;
use Sheba\Pos\Repositories\PosOrderPaymentRepository;

class Transfer
{
    /** @var PosOrderPaymentRepository $paymentRepo */
    private $paymentRepo;
    private $log;
    private $amount;
    /** @var PosOrder $order */
    private $order;

    public function __construct(PosOrderPaymentRepository $payment_repo)
    {
        $this->paymentRepo = $payment_repo;
    }

    /**
     * NEW CREATED ORDER
     *
     * @param PosOrder $order
     * @return $this
     */
    public function setOrder(PosOrder $order)
    {
        $this->order = $order;
        return $this;
    }

    public function setLog($log)
    {
        $this->log = $log;
        return $this;
    }

    public function setAmount($amount)
    {
        $this->amount = $amount;
        return $this;
    }

    public function process()
    {
        $data = [
            'pos_order_id' => $this->order->id,
            'amount' => $this->amount,
            'transaction_type' => 'Credit',
            'method' => 'transfer'
        ];
        $this->paymentRepo->save($data);
    }
}