<?php namespace Sheba\Resource\Service;


use App\Models\Job;

class ServiceList
{
    /** @var Job */
    private $job;

    public function setJob(Job $job)
    {
        $this->job = $job;
        return $this;
    }

    public function getServicesList()
    {
        $location = $this->job->partnerOrder->order->location->id;
        $services = $this->job->partnerOrder->partner->services()->whereHas('locations', function($q) use ($location) {
            $q->where('location_id', $location);
        })->select($this->getSelectColumnsOfService())->where('category_id', $this->job->category_id)->where(function ($q) {
            $q->where('publication_status', 1);
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
        $services = $this->filterExistingServicesAndOptions($services);
        return $services;
    }

    private function getSelectColumnsOfService()
    {
        return [
            'services.id',
            'name',
            'is_published_for_backend',
            'variable_type',
            'services.min_quantity',
            'services.variables',
            'is_verified',
            'is_published',
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
                $jobServices = $this->job->jobServices()->where('service_id', $service->id)->get()->toArray();
                if (count($jobServices) > 0) {
                    foreach ($jobServices as $jobService) {
                        $existing_options = json_decode($jobService['option']);
                        foreach ($existing_options as $key => $existing_option) {
                            if (in_array($existing_option, $service->questions[$key]['answers_index']->toArray())) {
                                $index_to_remove = array_search($existing_option, $service->questions[$key]['answers_index']->toArray(),true);
                                $service->questions[$key]['answers_index']->forget($index_to_remove);
                                $service->questions[$key]['answers']->forget($index_to_remove);
                            }
                        }
                    }
                    return $service;
                }
                else {
                    return $service;
                }
            }
        })->values();
        return $services;
    }
}