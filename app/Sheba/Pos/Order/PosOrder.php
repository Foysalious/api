<?php namespace Sheba\Pos\Order;

use Sheba\Repositories\Interfaces\PaymentLinkRepositoryInterface;


class PosOrder
{
    /** @var PaymentLinkRepositoryInterface */
    private $paymentLinkRepo;

    public function __construct()
    {
        $this->paymentLinkRepo = app(PaymentLinkRepositoryInterface::class);
    }


    /**
     * @param $posorder
     * @param $payment_link_target
     * @return mixed|\Sheba\PaymentLink\PaymentLinkTransformer
     */
    public function getPaymentLinks($payment_link_target)
    {
        $payment_link = $this->paymentLinkRepo->getPaymentLinksByPosOrders($payment_link_target);
        $key = $payment_link_target[0]->toString();
        if (array_key_exists($key, $payment_link)) {
            return $payment_link[$key];
        }
        return false;
    }

}