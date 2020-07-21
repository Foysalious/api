<?php namespace Sheba\Resource\Service;


use App\Models\Job;
use Illuminate\Http\Request;
use Sheba\Authentication\AuthUser;

class ServiceList
{
    /** @var Job */
    private $job;
    /** @var Request */
    private $request;

    public function setJob(Job $job)
    {
        $this->job = $job;
        return $this;
    }

    /**
     * @param Request $request
     * @return $this
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;
        return $this;
    }

    public function getServicesList()
    {
        $is_published_for_backend = $this->request->is_published_for_backend;
        $location = $this->job->partnerOrder->order->location->id;
        $services = $this->job->partnerOrder->partner->services()->whereHas('locations', function($q) use ($location) {
            $q->where('location_id', $location);
        })->select($this->getSelectColumnsOfService())->where('category_id', $this->job->category_id)->where(function ($q) use ($is_published_for_backend) {
            if (!$is_published_for_backend) $q->where('publication_status', 1);
            $q->orWhere('is_published_for_backend', 1);
        })->get();
        if (count($services) > 0) {
            $services->each(function (&$service) {
                if ($service->variable_type == 'Options') {
                    $service['questions'] = $this->formatServiceQuestionsAndAnswers($service);
                } else {
                    $service['questions'] = [];
                }
                array_forget($service, 'variables');
                removeRelationsAndFields($service);
            });
        }
        $services = $this->filterExistingServicesAndOptions($services); //For Now Only Fixed Services
        return $services;
    }

    private function getSelectColumnsOfService()
    {
        return [
            'services.id',
            'name',
            'variable_type',
            'services.min_quantity',
            'services.unit',
            'services.variables',
            'app_thumb'
        ];
    }

    private function formatServiceQuestionsAndAnswers($service)
    {
        $questions = collect();
        $variables = json_decode($service->variables);
        foreach ($variables->options as $key => $option) {
            $answers_with_index = $this->formatOptionAnswers(json_decode($service->pivot->options)[$key], $option);
            $questions->push(array('question' => $option->question, 'answers' => $answers_with_index['answers'], 'answers_index' => $answers_with_index['answers_index']));
        }
        return $questions;
    }

    private function formatOptionAnswers($options_from_pivot, $option)
    {
        $answers = collect();
        $answers_index = collect();
            $options = explode(',', $option->answers);
            foreach ($options as $key => $option) {
                if (in_array($key, $options_from_pivot)) {
                    $answers->push($option);
                    $answers_index->push($key);
                }
            }
        $answers_with_index = [];
        $answers_with_index['answers'] = $answers;
        $answers_with_index['answers_index'] = $answers_index;
        return $answers_with_index;
    }

    private function filterExistingServicesAndOptions($services)
    {
        $services = $services->filter(function ($service) {
            if ($service->variable_type == 'Fixed') {
                $jobService = $this->job->jobServices()->where('service_id',$service->id)->first();
                if (count($jobService) > 0) {
                    return $service->id !== $jobService->service_id;
                }
                else {
                    return $service;
                }
            }
            else {
                return $service;
            }
        })->values();
        return $services;
    }

    public function getAllServices()
    {
        /** @var AuthUser $auth_user */
        $auth_user = $this->request->auth_user;
        $resource = $auth_user->getResource();

        $services = $resource->firstPartner()->services;

        $services = $services->map(function ($service, $key) {
            $formatted_service = [
                'id' => $service->id,
                'name' => $service->name,
                'variable_type' => $service->variable_type,
                'min_quantity' => $service->min_quantity,
                'unit' => $service->unit,
                'app_thumb' => $service->app_thumb,
            ];
            if ($service->variable_type == 'Options') {
                $formatted_service['questions'] = $this->formatServiceQuestionsAndAnswers($service);
            } else {
                $formatted_service['questions'] = [];
            }
            return $formatted_service;
        });

        return $services;
    }
}