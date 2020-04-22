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
}