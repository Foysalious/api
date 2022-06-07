<?php namespace Sheba\Resource\Jobs\Updater;


use App\Models\Job;
use App\Models\Resource;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use Sheba\UserAgentInformation;

class StatusUpdater
{
    /** @var Resource */
    private $resource;
    /** @var Job */
    private $job;
    private $status;
    /** @var UserAgentInformation */
    private $userAgentInformation;


    public function setUserAgentInformation(UserAgentInformation $userAgentInformation)
    {
        $this->userAgentInformation = $userAgentInformation;
        return $this;
    }

    /**
     * @param Job $job
     * @return StatusUpdater
     */
    public function setJob(Job $job)
    {
        $this->job = $job;
        return $this;
    }

    public function setStatus($status)
    {
        $this->status = ucfirst($status);
        return $this;
    }

    public function setResource(Resource $resource)
    {
        $this->resource = $resource;
        return $this;
    }


    /**
     * @return StatusUpdateResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function update()
    {
        $status_update_response = new StatusUpdateResponse();
        if ($this->hasError()) return $status_update_response->setResponse(['code' => 400, 'message' => 'Bad request']);
        $client = new Client();
        $res = $client->request('POST', config('sheba.admin_url') . '/api/job/' . $this->job->id . '/change-status',
            [
                'form_params' => [
                    'resource_id' => $this->resource->id,
                    'remember_token' => $this->resource->remember_token,
                    'status' => $this->status,
                    'created_by_type' => class_basename($this->resource),
                    'partner_id' => $this->job->partnerOrder->partner_id,
                    'portal_name' => $this->userAgentInformation->getPortalName(),
                    'user_agent' => $this->userAgentInformation->getUserAgent(),
                    'ip' => $this->userAgentInformation->getIp()
                ]
            ]);
        $responseBody = json_decode($res->getBody(), 1);
        $statusResponse = (new StatusUpdateResponse())->setResponse($responseBody);
        if ($responseBody['code'] === 422 && isset($responseBody['msg'])) $statusResponse->setMessage($responseBody['msg']);
        return $statusResponse;
    }

    private function hasError()
    {
        return $this->status == $this->job->status;
    }
}