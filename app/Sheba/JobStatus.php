<?php

namespace App\Sheba;

use App\Models\Job;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class JobStatus
{
    private $job;
    private $updated_by;

    public function __construct(Job $job)
    {
        $this->job = $job;
    }

    public function __set($name, $value)
    {
        $this->$name = $value;
    }

    public function update($status)
    {
        try {
            $client = new Client();
            $data = [
                'remember_token' => $this->updated_by->remember_token,
                'status' => $status
            ];
            if (class_basename($this->updated_by) == 'Customer') {
                $data['customer_id'] = $this->updated_by->id;
            } elseif (class_basename($this->updated_by) == 'Resource') {
                $data['resource_id'] = $this->updated_by->id;
            }
            $result = $client->request('POST', env('SHEBA_BACKEND_URL') . '/api/job/' . $this->job->id . '/change-status', ['form_params' => $data]);
            return json_decode($result->getBody());
        } catch (RequestException $e) {
            return false;
        }
    }

}