<?php namespace Sheba\ServiceRequest;

use Illuminate\Support\Facades\Validator as LaravelValidator;

class Validator
{
    private $services;
    private $rules;
    private $rentACarRules;
    /** @var LaravelValidator */
    private $errors;

    public function __construct()
    {
        $this->rules = [
            'id' => 'required|numeric',
            'option' => 'array',
            'quantity' => 'required|numeric'
        ];
        $this->rentACarRules = array_merge($this->rules, [
            'pick_up_location_geo' => 'required',
            'pick_up_address' => 'required'
        ]);
    }

    public function setServices(array $services)
    {
        $this->services = $services;
        return $this;
    }

    private function setErrors($errors)
    {
        $this->errors = $errors;
        return $this;
    }

    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @return LaravelValidator
     */
    public function hasError()
    {
        return $this->errors;
    }


    public function validate()
    {
        foreach ($this->services as $service) {
            $validator = LaravelValidator::make($service, $this->getRules($service));
            if (!$validator->passes()) $this->setErrors($validator);
        }
    }

    private function getRules($service)
    {
        if (isset($service['id']) && in_array($service['id'], config('sheba.car_rental')['destination_fields_service_ids'])) return $this->getDestinationRules();
        if (isset($service['id']) && in_array($service['id'], config('sheba.car_rental.service_ids'))) return $this->rentACarRules;
        return $this->rules;
    }

    private function getDestinationRules()
    {
        return array_merge($this->rentACarRules, [
            'destination_location_geo' => 'required',
            'destination_address' => 'required'
        ]);
    }


}