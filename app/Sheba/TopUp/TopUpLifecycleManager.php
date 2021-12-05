<?php namespace Sheba\TopUp;


use Carbon\Carbon;
use Illuminate\Support\Facades\Redis;
use Sheba\Dal\TopupOrder\FailedReason;
use Sheba\TopUp\Gateway\HasIpn;
use Sheba\TopUp\Vendor\Response\Ipn\FailResponse;
use Sheba\TopUp\Vendor\Response\Ipn\IpnResponse;
use Sheba\TopUp\Vendor\Response\Ipn\SuccessResponse;
use DB;
use Throwable;

class TopUpLifecycleManager extends TopUpManager
{
    /**
     * @param FailResponse $fail_response
     * @throws Throwable
     */
    public function fail(FailResponse $fail_response)
    {
        if ($this->topUpOrder->isFailed()) return;

        $this->doTransaction(function () use ($fail_response) {
            $this->statusChanger->failed(FailedReason::GATEWAY_ERROR, $fail_response->getTransactionDetailsString());
            if ($this->topUpOrder->isAgentDebited()) $this->refund();
            $this->getVendor()->refill($this->topUpOrder->amount);
        });
    }

    /**
     * @param SuccessResponse $success_response
     * @throws Throwable
     */
    public function success(SuccessResponse $success_response)
    {
        if ($this->topUpOrder->isSuccess()) return;

        $this->doTransaction(function () use ($success_response) {
            $details = $success_response->getTransactionDetailsString();
            $id = $success_response->getUpdatedTransactionId();
            $this->statusChanger->successful($details, $id);
        });
    }

    /**
     * @return IpnResponse | void
     * @throws Throwable
     */
    public function reload()
    {
        if (!$this->topUpOrder->canRefresh()) return;
        $vendor = $this->getVendor();
        $response = $vendor->enquire($this->topUpOrder);
        $response->handleTopUp();
        return $response;
    }

    /**
     * @throws Throwable
     */
    public function handleIpn(HasIpn $gateway, $request_data)
    {
        $ipn_response = $gateway->buildIpnResponse($request_data);
        $ipn_response->setResponse($request_data);
        $ipn_response->handleTopUp();
        $this->logIpn($ipn_response, $request_data);
    }

    private function logIpn(IpnResponse $ipn_response, $request_data)
    {
        $key = 'Topup::' . ($ipn_response instanceof FailResponse ? "Failed:failed" : "Success:success") . "_";
        $key .= Carbon::now()->timestamp . '_' . $ipn_response->getTopUpOrder()->id;
        Redis::set($key, json_encode($request_data));
    }
}
