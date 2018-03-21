<?php


namespace App\GraphQL\Type;

use GraphQL;
use \Folklore\GraphQL\Support\Type as GraphQlType;
use GraphQL\Type\Definition\Type;

class ReviewType extends GraphQlType
{
    protected $attributes = [
        'name' => 'Review'
    ];

    public function fields()
    {
        return [
            'id' => ['type' => Type::int()],
            'rating' => ['type' => Type::int()],
            'review' => ['type' => Type::string()],
            'customer' => ['type' => GraphQL::type('Customer')],
            'partner' => ['type' => GraphQL::type('Partner')],
            'category' => ['type' => GraphQL::type('Category')],
            'job' => ['type' => GraphQL::type('Job')]
        ];
    }

    protected function resolveReviewField($root, $args)
    {
        if ($root->rate != null) {

        }
        return null;
    }
}