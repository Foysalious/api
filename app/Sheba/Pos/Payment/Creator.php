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

    public function credit(array $data, $is_partner_migrated = false)
    {
        $data['transaction_type'] = 'Credit';
        $this->create($data, $is_partner_migrated);
    }

    public function debit(array $data, $is_partner_migrated = false)
    {
        $data['transaction_type'] = 'Debit';
        $this->create($data, $is_partner_migrated);
    }

    private function create(array $data, $is_partner_migrated)
    {
        $is_partner_migrated ? $this->paymentRepo->saveToPosOrder($data) : $this->paymentRepo->save($data);
    }
}