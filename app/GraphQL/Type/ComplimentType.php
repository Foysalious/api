<?php

namespace App\GraphQL\Type;

use \Folklore\GraphQL\Support\Type as GraphQlType;
use GraphQL\Type\Definition\Type;


class ComplimentType extends GraphQlType
{
    protected $attributes = [
        'name' => 'Compliment',
        'description' => 'Sheba Location'
    ];

    public function fields()
    {
        return [
            'name' => ['type' => Type::string()],
            'badge' => ['type' => Type::string()],
        ];
    }

    public function resolveNameField($root)
    {
        return $root->answer;
    }

    public function resolveBadgeField($root)
    {
        return $root->badge;
    }
}