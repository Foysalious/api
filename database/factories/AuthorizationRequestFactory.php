<?php

namespace Database\Factories;

use Sheba\Dal\AuthorizationRequest\AuthorizationRequest;

class AuthorizationRequestFactory extends Factory
{
    protected $model = AuthorizationRequest::class;

    public function definition(): array
    {
        return [
            'purpose'    => 'login',
            'status'     => 'success',
            'created_at' => $this->now,
            'updated_at' => $this->now,
        ];
    }
}
