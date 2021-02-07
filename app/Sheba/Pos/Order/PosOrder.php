<?php namespace Sheba\Pos\Order;


use App\Transformers\PosOrderTransformer;
use Sheba\Dal\POSOrder\SalesChannels;
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
    public function mapPaymentLinkData($posorder, $payment_link_target)
    {
        $payment_link = $this->paymentLinkRepo->getPaymentLinksByPosOrders($payment_link_target);
        $key = $posorder['payment_link_target']->toString();
        if (array_key_exists($key, $payment_link)) {
            return $payment_link[$key][0];
        }
    }

}