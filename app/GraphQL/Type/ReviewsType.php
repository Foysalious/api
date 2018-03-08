<?php


namespace App\GraphQL\Type;

use GraphQL;
use \Folklore\GraphQL\Support\Type as GraphQlType;
use GraphQL\Type\Definition\Type;

class ReviewsType extends GraphQlType
{
    protected $attributes = [
        'name' => 'Reviews'
    ];

    public function fields()
    {
        return [
            'id' => ['type' => Type::int()],
            'rating' => ['type' => Type::int()],
            'review' => ['type' => Type::string()],
            'review_title' => ['type' => Type::string()],
            'customer' => ['type' => GraphQL::type('Customer')],
            'partner' => ['type' => GraphQL::type('Partner')]
        ];
    }
}