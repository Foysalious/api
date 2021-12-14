<?php namespace Sheba\TopUp\Vendor\Response\Ipn;

abstract class SuccessResponse extends IpnResponse
{
    public function isFailed(): bool
    {
        return false;
    }

    protected function _handleTopUp()
    {
        $this->topUp->success($this);
    }

    /**
     * @return string | null
     */
    public function getUpdatedTransactionId()
    {
        return null;
    }
}
