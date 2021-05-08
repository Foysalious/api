<?php namespace Sheba\Pos\Payment;

use Sheba\Pos\Repositories\PosOrderPaymentRepository;

class Creator
{
    /** @var PosOrderPaymentRepository $paymentRepo */
    private $paymentRepo;

    public function __construct(PosOrderPaymentRepository $payment_repo)
    {
        $this->paymentRepo = $payment_repo;
    }

    public function credit(array $data, $is_new_system_pos_order = false)
    {
        $data['transaction_type'] = 'Credit';
        $this->create($data, $is_new_system_pos_order);
    }

    public function debit(array $data, $is_new_system_pos_order = false)
    {
        $data['transaction_type'] = 'Debit';
        $this->create($data, $is_new_system_pos_order);
    }

    private function create(array $data, $is_new_system_pos_order)
    {
        $is_new_system_pos_order ? $this->paymentRepo->saveToNewPosOrderSystem($data) : $this->paymentRepo->save($data);
    }
}