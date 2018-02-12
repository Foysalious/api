<?php

namespace App\GraphQL\Type;

use \Folklore\GraphQL\Support\Type as GraphQlType;
use GraphQL\Type\Definition\Type;

class CustomerType extends GraphQlType
{
    protected $attributes = [
        'name' => 'Customer',
        'description' => 'Sheba customer'
    ];

    public function fields()
    {
        return [
            'id' => ['type' => Type::int()],
            'name' => ['type' => Type::string()],
            'picture' => ['type' => Type::string()],
        ];
    }

    protected function resolveNameField($root, $args)
    {
        return $root->profile->name;
    }

    protected function resolvePictureField($root, $args)
    {
        return $root->profile->pro_pic;
    }
}