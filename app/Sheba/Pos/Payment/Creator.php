<?php namespace Sheba\Pos\Payment;

use Sheba\Pos\Repositories\PosOrderPaymentRepository;

class Creator
{
    /**
     * @var PosOrderPaymentRepository
     */
    private $paymentRepo;

    public function __construct(PosOrderPaymentRepository $payment_repo)
    {
        $this->paymentRepo = $payment_repo;
    }

    public function create(array $data)
    {
        $this->paymentRepo->save($data);
    }
}