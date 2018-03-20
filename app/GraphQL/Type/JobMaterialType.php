<?php

namespace App\GraphQL\Type;

use \Folklore\GraphQL\Support\Type as GraphQlType;
use GraphQL\Type\Definition\Type;

class JobMaterialType extends GraphQlType
{
    protected $attributes = [
        'name' => 'JobMaterial'
    ];

    public function fields()
    {
        return [
            'name' => ['type' => Type::string()],
            'price' => ['type' => Type::float()],
        ];
    }

    protected function resolveNameField($root, $args)
    {
        return $root->material_name;
    }

    protected function resolvePriceField($root, $args)
    {
        return (float)$root->material_price;
    }
}