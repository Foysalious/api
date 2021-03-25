<?php namespace Sheba\TopUp\Vendor\Response\Ipn;

abstract class SuccessResponse extends IpnResponse
{
    public function isFailed()
    {
        return false;
    }

    protected function _handleTopUp()
    {
        $this->topUp->success($this);
    }
}
