<?php namespace Sheba\TopUp\Vendor\Response\Ipn;

abstract class FailResponse extends IpnResponse
{
    public function isFailed()
    {
        return true;
    }

    protected function _handleTopUp()
    {
        $this->topUp->fail($this);
    }
}
