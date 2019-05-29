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

    public function credit(array $data)
    {
        $data['transaction_type'] = 'Credit';
        $this->create($data);
    }

    public function debit(array $data)
    {
        $data['transaction_type'] = 'Debit';
        $this->create($data);
    }

    private function create(array $data)
    {
        $this->paymentRepo->save($data);
    }
}