<?php

namespace App\GraphQL\Type;

use \Folklore\GraphQL\Support\Type as GraphQlType;
use GraphQL\Type\Definition\Type;

class OrderMessageType extends GraphQlType
{
    protected $attributes = [
        'name' => 'OrderMessage',
        'description' => 'Order Message'
    ];

    public function fields()
    {
        return [
            'status' => ['type' => Type::string()],
            'log' => ['type' => Type::string()],
            'type' => ['type' => Type::string()]
        ];
    }
}