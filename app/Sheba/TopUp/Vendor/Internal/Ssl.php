<?php namespace Sheba\TopUp\Vendor\Internal;

use App\Models\TopUpOrder;
use Sheba\TopUp\Vendor\Response\TopUpResponse;
use Sheba\TopUp\Vendor\VendorFactory;

trait Ssl
{
    private $ssl;

    public function __construct(SslClient $ssl)
    {
        $this->ssl = $ssl;
    }

    /**
     * @param TopUpOrder $topup_order
     * @return TopUpResponse
     * @throws \SoapFault
     */
    public function recharge(TopUpOrder $topup_order): TopUpResponse
    {
        return $this->ssl->recharge($topup_order);
    }

    public function getTopUpInitialStatus()
    {
        return config('topup.status.pending')['sheba'];
    }

    /**
     * @param $amount
     * @throws \Exception
     */
    public function deductAmount($amount)
    {
        VendorFactory::sslVendors()->update(['amount' => $this->model->amount - $amount]);
    }

    public function refill($amount)
    {
        VendorFactory::sslVendors()->increment('amount', $amount);
    }
}
