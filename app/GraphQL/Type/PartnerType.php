<?php

namespace App\GraphQL\Type;

use Carbon\Carbon;
use GraphQL;
use \Folklore\GraphQL\Support\Type as GraphQlType;
use GraphQL\Type\Definition\Type;
use Redis;

class PartnerType extends GraphQlType
{
    protected $attributes = [
        'name' => 'Partner',
        'description' => 'Sheba Partner'
    ];

    public function fields()
    {
        return [
            'id' => ['type' => Type::int()],
            'name' => ['type' => Type::string()]
        ];
    }
}