<?php namespace Sheba\TopUp\Gateway;

use Sheba\TopUp\Exception\UnknownIpnStatusException;
use Sheba\TopUp\Vendor\Response\Ipn\IpnResponse;

interface HasIpn
{
    /**
     * @param $request_data
     * @return IpnResponse
     * @throws UnknownIpnStatusException
     */
    public function buildIpnResponse($request_data);
}