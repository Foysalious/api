<?php

namespace App\Sheba;

use App\Models\Job;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\Request;

class JobStatus
{
    private $job;
    private $updated_by;
    public $request;

    public function __construct(Job $job, Request $request)
    {
        $this->job = $job;
        $this->request = $request;
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
                $data['created_by_type'] = 'Customer';
            } elseif (class_basename($this->updated_by) == 'Resource') {
                $data['resource_id'] = $this->updated_by->id;
                $data['created_by_type'] = 'Resource';
            }
            $result = $client->request('POST', env('SHEBA_BACKEND_URL') . '/api/job/' . $this->job->id . '/change-status',
                [
                    'form_params' => array_merge((new UserRequestInformation($this->request))->getInformationArray(), $data)
                ]);
            return json_decode($result->getBody());
        } catch (RequestException $e) {
            return false;
        }
    }

}