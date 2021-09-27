<?php namespace Sheba\Resource\Service;


use App\Models\HyperLocal;
use App\Models\Job;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ServiceList
{
    /** @var Job */
    private $job;
    /** @var Request */
    private $request;
    private $resource;
    private $geo;
    private $categoryId;
    /** @var array */
    private $serviceIds = [];

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

    public function setCategoryId($category_id)
    {
        $this->categoryId = $category_id;
        return $this;
    }

    public function setServiceIds(array $ids)
    {
        $this->serviceIds = $ids;
        return $this;
    }

    public function getServicesList()
    {
        $cs_service_ids = DB::table('crosssale_services')->whereIn('service_id', $this->serviceIds)->get();
        $is_published_for_backend = $this->request->is_published_for_backend;
        $location = $this->job->partnerOrder->order->location->id;
        $services = $cs_service_ids ? $this->job->partnerOrder->partner->services()->whereHas('locations', function ($q) use ($location) {
            $q->where('location_id', $location);
        })->select($this->getSelectColumnsOfService())->where('category_id', $this->job->category_id)->where(function ($q) use ($is_published_for_backend) {
            if (!$is_published_for_backend) $q->where('publication_status', 1);
            $q->orWhere('is_published_for_backend', 1);
        })->get() : $this->job->partnerOrder->partner->services()->whereHas('locations', function ($q) use ($location) {
            $q->where('location_id', $location);
        })->select($this->getSelectColumnsOfService())->where('category_id', $this->job->category_id)->where('is_add_on', 0)->where(function ($q) use ($is_published_for_backend) {
            if (!$is_published_for_backend) $q->where('publication_status', 1);
            $q->orWhere('is_published_for_backend', 1);
        })->get();
        if (count($services) > 0) {
            $services->each(function (&$service) {
                $variables = json_decode($service->variables);
                if ($service->variable_type == 'Options') {
                    $service['questions'] = $this->formatServiceQuestionsAndAnswers($service);
                    $service['option_prices'] = $this->formatOptionWithPrice(json_decode($service->pivot->prices));
                    $service['fixed_price'] = null;
                } else {
                    $service['questions'] = [];
                    $service['option_prices'] = [];
                    $service['fixed_price'] = is_object($variables->price) ? $variables->price : (double)$variables->price;
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
            'app_thumb',
            'category_id',
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
                $jobService = $this->job->jobServices()->where('service_id', $service->id)->first();
                if (count($jobService) > 0) {
                    return $service->id !== $jobService->service_id;
                } else {
                    return $service;
                }
            } else {
                return $service;
            }
        })->values();
        return $services;
    }

    public function setResource($resource)
    {
        $this->resource = $resource;
        return $this;
    }

    public function setGeo($geo)
    {
        $this->geo = $geo;
        return $this;
    }

    public function getAllServices()
    {
        $hyperLocation = HyperLocal::insidePolygon($this->geo->getLat(), $this->geo->getLng())->with('location')->first();

        if (is_null($hyperLocation)) return null;

        $location = $hyperLocation->location->id;

        $services = $this->resource->firstPartner()->services()->select($this->getSelectColumnsOfService())->where(function ($q) {
            $q->where('publication_status', 1)->where('is_add_on', 0);
        })->whereHas('locations', function ($q) use ($location) {
            $q->where('locations.id', $location);
        });
        if ($this->categoryId) $services->where('category_id', $this->categoryId);
        $services = $services->get();
        $services->each(function (&$service) {
            $variables = json_decode($service->variables);
            if ($service->variable_type == 'Options') {
                $service['questions'] = $this->formatServiceQuestionsAndAnswers($service);
                $service['option_prices'] = $this->formatOptionWithPrice(json_decode($service->pivot->prices));
                $service['fixed_price'] = null;
            } else {
                $service['questions'] = $service['option_prices'] = [];
                $service['fixed_price'] = is_object($variables->price) ? $variables->price : (double)$variables->price;
            }
            $service['is_rent_a_car'] = $service->category->isRentCar();
            array_forget($service, 'variables');
            removeRelationsAndFields($service);
        });

        return $services;
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