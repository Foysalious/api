<?php

namespace Database\Factories;

use Carbon\Carbon;
use Sheba\Dal\AuthorizationToken\AuthorizationToken;

class AuthorizationTokenFactory extends Factory
{
    protected $model = AuthorizationToken::class;

    public function definition(): array
    {
        return [
            'valid_till'         => Carbon::now()->addDay(),
            'refresh_valid_till' => Carbon::now()->addDays(7),
            'is_blacklisted'     => 0,
            'created_at'         => Carbon::now(),
            'updated_at'         => Carbon::now(),
            'updated_by'         => 1,
        ];
    }
}
