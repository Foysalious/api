<?php

namespace App\graphQL\Type;

use \Folklore\GraphQL\Support\Type as GraphQlType;
use GraphQL\Type\Definition\Type;

class ServiceType extends GraphQlType
{
    protected $attributes = [
        'name' => 'Service',
        'description' => 'A user'
    ];

    /*
    * Uncomment following line to make the type input object.
    * http://graphql.org/learn/schema/#input-types
    */
    // protected $inputObject = true;

    public function fields()
    {
        return [
            'id' => [
                'type' => Type::int()
            ],
            'name' => [
                'type' => Type::string(),
            ],
            'slug' => [
                'type' => Type::string()
            ],
            'description' => [
                'type' => Type::string()
            ],
            'unit' => [
                'type' => Type::string()
            ],
            'min_quantity' => [
                'type' => Type::float()
            ],
            'thumb' => [
                'type' => Type::string()
            ],
            'banner' => [
                'type' => Type::string()
            ],
            'faqs' => [
                'type' => Type::string()
            ],
            'variable_type' => [
                'type' => Type::string()
            ],
            'variables' => [
                'type' => Type::string()
            ],
        ];
    }

    // If you want to resolve the field yourself, you can declare a method
    // with the following format resolve[FIELD_NAME]Field()
    protected function resolveMinQuantityField($root, $args)
    {
        return (float)($root->min_quantity);
    }
}