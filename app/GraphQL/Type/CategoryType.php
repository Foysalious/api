<?php

namespace App\GraphQL\Type;

use GraphQL;
use \Folklore\GraphQL\Support\Type as GraphQlType;
use GraphQL\Type\Definition\Type;

class CategoryType extends GraphQlType
{
    protected $attributes = [
        'name' => 'Category',
        'description' => 'Sheba Category'
    ];

    public function fields()
    {
        return [
            'id' => ['type' => Type::int()],
            'name' => ['type' => Type::string()],
            'short_description' => ['type' => Type::string()],
            'long_description' => ['type' => Type::string()],
            'thumb' => ['type' => Type::string()],
            'app_thumb' => ['type' => Type::string()],
            'banner' => ['type' => Type::string()],
            'app_banner' => ['type' => Type::string()],
            'publication_status' => ['type' => Type::int()],
            'icon' => ['type' => Type::int()],
            'questions' => ['type' => Type::int()],
            'reviews' => [
                'args' => [
                    'rating' => ['type' => Type::listOf(Type::int())],
                    'hasReview' => ['type' => Type::boolean()]
                ],
                'type' => Type::listOf(GraphQL::type('Reviews'))
            ]
        ];
    }

    protected function resolveReviewsField($root, $args)
    {
        $root->load(['reviews' => function ($q) use ($args) {
            if (isset($args['rating'])) {
                $q->whereIn('rating', $args['rating']);
            }
            if (isset($args['hasReview'])) {
                $q->hasReview();
            }
            return $q->with('customer.profile');
        }]);
        return $root->reviews;
    }
}