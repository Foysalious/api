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
        $services = $this->job->partnerOrder->partner->services()->select($this->getSelectColumnsOfService())->where('category_id', $this->job->category_id)->where(function ($q) {
            $q->where('publication_status', 1);
            $q->orWhere('is_published_for_backend', 1);
        })->get();
        if (count($services) > 0) {
            $services->each(function (&$service) {
                $variables = json_decode($service->variables);
                if ($service->variable_type == 'Options') {
                    $service['questions'] = $this->formatServiceQuestions($variables->options);
                    $service['option_prices'] = $this->formatOptionWithPrice(json_decode($service->pivot->prices));
                    $service['fixed_price'] = null;
                } else {
                    $service['questions'] = $service['option_prices'] = [];
                    $service['fixed_price'] = (double)$variables->price;
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

    private function formatServiceQuestions($options)
    {
        $questions = collect();
        foreach ($options as $option) {
            $questions->push(array(
                'question' => $option->question,
                'answers' => explode(',', $option->answers)
            ));
        }
        return $questions;
    }

    private function formatOptionWithPrice($prices)
    {
        $options = collect();
        foreach ($prices as $key => $price) {
            $options->push(array(
                'option' => collect(explode(',', $key))->map(function ($key) {
                    return (int)$key;
                }),
                'price' => (double)$price
            ));
        }
        return $options;
    }
}