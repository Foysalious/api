<?php namespace Sheba\TopUp\Vendor\Response\Ipn;


use App\Models\TopUpOrder;
use Sheba\TopUp\TopUpLifecycleManager;

abstract class IpnResponse
{
    /** @var TopUpLifecycleManager */
    protected $topUp;

    /** @var array $response */
    protected $response;
    /** @var TopUpOrder */
    protected $topUpOrder;

    public function __construct(TopUpLifecycleManager $top_up)
    {
        $this->topUp = $top_up;
    }

    public function setResponse($response)
    {
        $this->response = $response;
    }

    public function getTransactionDetails()
    {
        return $this->response;
    }

    public function getResponse()
    {
        return $this->response;
    }

    public function setTopUpOrder(TopUpOrder $order)
    {
        $this->topUpOrder = $order;
        return $this;
    }

    public function getTopUpOrder(): TopUpOrder
    {
        if (is_null($this->topUpOrder)) $this->topUpOrder = $this->findTopUpOrder();
        return $this->topUpOrder;
    }

    abstract protected function findTopUpOrder(): TopUpOrder;

    abstract public function isFailed();

    /**
     * @return string
     */
    public function getTransactionDetailsString()
    {
        return json_encode($this->getTransactionDetails());
    }

    /**
     * @throws \Throwable
     * @return void
     */
    public function handleTopUp()
    {
        $this->topUp->setTopUpOrder($this->getTopUpOrder());
        $this->_handleTopUp();
    }

    /**
     * @throws \Throwable
     * @return void
     */
    abstract protected function _handleTopUp();
}
