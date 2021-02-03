<?php namespace Factory;

use Sheba\Dal\AuthorizationRequest\AuthorizationRequest;

class AuthorizationRequestFactory extends Factory
{
    protected function getModelClass()
    {
        return AuthorizationRequest::class;
    }

    protected function getData()
    {
        return [
            'purpose' => 'login',
            'status' => 'success',
            'created_at' => $this->now,
            'updated_at' => $this->now
        ];
    }
}