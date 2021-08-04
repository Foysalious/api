<?php namespace Sheba\Pos\Payment;

use Sheba\Pos\Order\PosOrderTypes;
use Sheba\Pos\Repositories\PosOrderPaymentRepository;

class Creator
{
    /** @var PosOrderPaymentRepository $paymentRepo */
    private $paymentRepo;

    public function __construct(PosOrderPaymentRepository $payment_repo)
    {
        $this->paymentRepo = $payment_repo;
    }

    public function credit(array $data, $pos_order_type = PosOrderTypes::OLD_SYSTEM)
    {
        $data['transaction_type'] = 'Credit';
        $this->create($data, $pos_order_type);
    }

    public function debit(array $data, $pos_order_type = PosOrderTypes::OLD_SYSTEM)
    {
        $data['transaction_type'] = 'Debit';
        $this->create($data, $pos_order_type);
    }

    private function create(array $data, $pos_order_type)
    {
        $pos_order_type == PosOrderTypes::NEW_SYSTEM ? $this->paymentRepo->saveToNewPosOrderSystem($data) : $this->paymentRepo->save($data);
    }
}