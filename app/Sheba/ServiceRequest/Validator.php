<?php namespace Sheba\ServiceRequest;

use Illuminate\Support\Facades\Validator as LaravelValidator;

class Validator
{
    private $services;
    private $rules;
    /** @var LaravelValidator */
    private $errors;

    public function __construct()
    {
        $this->rules = [
            'id' => 'required|numeric',
            'option' => 'array',
            'quantity' => 'required|numeric'
        ];
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
            $validator = LaravelValidator::make($service, $this->rules);
            if (!$validator->passes()) $this->setErrors($validator);
        }
    }


}