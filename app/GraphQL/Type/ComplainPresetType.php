<?php

namespace App\GraphQL\Type;

use GraphQL;
use \Folklore\GraphQL\Support\Type as GraphQlType;
use GraphQL\Type\Definition\Type;

class ComplainPresetType extends GraphQlType
{
    protected $attributes = [
        'name' => 'ComplainPreset',
        'description' => 'Complain Presets'
    ];

    public function fields()
    {
        return [
            'id' => ['type' => Type::int()],
            'name' => ['type' => Type::string()]
        ];
    }
}