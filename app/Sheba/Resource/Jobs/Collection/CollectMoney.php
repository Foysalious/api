<?php namespace Sheba\Resource\Jobs\Collection;


use App\Models\Job;
use App\Models\Resource;
use GuzzleHttp\Client;
use Sheba\UserAgentInformation;

class CollectMoney
{
    /** @var Resource */
    private $resource;
    /** @var Job */
    private $job;
    /** @var UserAgentInformation */
    private $userAgentInformation;
    private $collectionAmount;


    public function setUserAgentInformation(UserAgentInformation $userAgentInformation)
    {
        $this->userAgentInformation = $userAgentInformation;
        return $this;
    }

    /**
     * @param Job $job
     * @return $this
     */
    public function setJob(Job $job)
    {
        $this->job = $job;
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
        $res = $client->request('POST', config('sheba.admin_url') . '/api/partner-order/' . $this->job->partner_order_id . '/collect',
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
        if ($response) $collect_money_response->setCode($response['code'])->setMessage($response['msg']);
        return $collect_money_response;
    }

}