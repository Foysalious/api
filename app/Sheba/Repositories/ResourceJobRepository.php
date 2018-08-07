<?php

namespace Sheba\Repositories;

use App\Models\Job;
use App\Models\PartnerOrder;
use App\Models\Resource;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class ResourceJobRepository extends BaseRepository
{
    private $resource;
    private $jobStatuses;

    public function __construct(Resource $resource)
    {
        parent::__construct();
        $this->resource = $resource;
        $this->setModifier($resource);
    }

    public function changeJobStatus(Job $job, $new_status)
    {
        $form_data = $this->withRequestIdentificationData(array_merge(['status' => $new_status], $this->getResourceInfo()));
        $url = config('sheba.admin_url') . '/api/job/' . $job->id . '/change-status';
        try {
            $client = new Client();
            $response = $client->request('POST', $url, array('form_params' => $form_data));
            return json_decode($response->getBody());
        } catch (RequestException $e) {
            $sentry = app('sentry');
            $sentry->user_context(['data' => $form_data]);
            $sentry->captureException($e);
            return null;
        }
    }


    public function collectMoney(PartnerOrder $partner_order, $collection_amount)
    {
        $form_data = $this->withRequestIdentificationData(array_merge(['partner_collection' => $collection_amount], $this->getResourceInfo()));
        $url = config('sheba.admin_url') . '/api/partner-order/' . $partner_order->id . '/collect';
        try {
            $client = new Client();
            $response = $client->request('POST', $url, array('form_params' => $form_data));
            return json_decode($response->getBody());
        } catch (RequestException $e) {
            $sentry = app('sentry');
            $sentry->user_context(['data' => $form_data]);
            $sentry->captureException($e);
            return null;
        }
    }

    private function getResourceInfo()
    {
        return [
            'resource_id' => $this->resource->id,
            'remember_token' => $this->resource->remember_token,
        ];
    }

}