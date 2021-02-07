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
     */
    public function mapPaymentLinkData(&$posorder, $payment_link_target)
    {

        $payment_link = $this->paymentLinkRepo->getPaymentLinksByPosOrders($payment_link_target);
        if (array_key_exists('payment_link_target', $posorder)) {

            $key = $posorder['payment_link_target']->toString();
            if (array_key_exists($key, $payment_link)) {
                (new PosOrderTransformer())->addPaymentLinkDataToOrder($posorder, $payment_link[$key][0]);
            }
            unset($posorder['payment_link_target']);
        }
        return $posorder;

    }

}