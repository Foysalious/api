<?php namespace Sheba\Resource\Jobs\Collection;


use App\Models\Job;
use App\Models\PartnerOrder;
use App\Models\Resource;
use GuzzleHttp\Client;
use Sheba\UserAgentInformation;

class CollectMoney
{
    /** @var Resource */
    private $resource;
    /** @var UserAgentInformation */
    private $userAgentInformation;
    private $collectionAmount;
    /** @var PartnerOrder */
    private $partnerOrder;


    public function setUserAgentInformation(UserAgentInformation $userAgentInformation)
    {
        $this->userAgentInformation = $userAgentInformation;
        return $this;
    }

    /**
     * @param PartnerOrder $partnerOrder
     * @return CollectMoney
     */
    public function setPartnerOrder($partnerOrder)
    {
        $this->partnerOrder = $partnerOrder;
        return $this;
    }

    public function setResource(Resource $resource)
    {
        $this->resource = $resource;
        return $this;
    }

    public function setCollectionAmount($collection_amount)
    {
        $this->collectionAmount = $collection_amount;
        return $this;
    }

    /**
     * @return CollectMoneyResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function collect()
    {
        $client = new Client();
        $res = $client->request('POST', config('sheba.admin_url') . '/api/partner-order/' . $this->partnerOrder->id . '/collect',
            [
                'form_params' => [
                    'resource_id' => $this->resource->id,
                    'remember_token' => $this->resource->remember_token,
                    'partner_collection' => $this->collectionAmount,
                    'created_by_type' => get_class($this->resource),
                    'portal_name' => $this->userAgentInformation->getPortalName(),
                    'user_agent' => $this->userAgentInformation->getUserAgent(),
                    'ip' => $this->userAgentInformation->getIp()
                ]
            ]);
        $collect_money_response = new CollectMoneyResponse();
        $response = json_decode($res->getBody(), 1);
        if ($response) {
            $collect_money_response->setCode($response['code']);
            if (isset($response['msg'])) $collect_money_response->setMessage($response['msg']);
            if (isset($response['message'])) $collect_money_response->setMessage($response['message']);
        }
        return $collect_money_response;
    }

    public function collectPartial()
    {
        $client = new Client();
        $res = $client->request('POST', config('sheba.admin_url') . '/api/partner-order/' . $this->partnerOrder->id . '/partial-collect',
            [
                'form_params' => [
                    'resource_id' => $this->resource->id,
                    'remember_token' => $this->resource->remember_token,
                    'partner_collection' => $this->collectionAmount,
                    'created_by_type' => get_class($this->resource),
                    'portal_name' => $this->userAgentInformation->getPortalName(),
                    'user_agent' => $this->userAgentInformation->getUserAgent(),
                    'ip' => $this->userAgentInformation->getIp()
                ]
            ]);
        $collect_money_response = new CollectMoneyResponse();
        $response = json_decode($res->getBody(), 1);
        if ($response) {
            $collect_money_response->setCode($response['code']);
            if (isset($response['msg'])) $collect_money_response->setMessage($response['msg']);
            if (isset($response['message'])) $collect_money_response->setMessage($response['message']);
        }
        return $collect_money_response;
    }

}
