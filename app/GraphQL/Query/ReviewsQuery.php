<?php

namespace App\GraphQL\Query;

use App\Models\Review;
use GraphQL;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Folklore\GraphQL\Support\Query;

class ReviewsQuery extends Query
{
    protected $attributes = [
        'name' => 'reviews'
    ];

    public function type()
    {
        return Type::listOf(GraphQL::type('Review'));
    }

    public function args()
    {
        return [
            'category_id' => ['name' => 'category_id', 'type' => Type::int()],
            'rating' => ['type' => Type::listOf(Type::int())]
        ];
    }

    public function resolve($root, $args, $context, ResolveInfo $info)
    {
        $reviews = Review::query();
        $reviews->with('customer.profile');
        $where = function ($query) use ($args) {
            if (isset($args['category_id'])) {
                $query->where('category_id', $args['category_id']);
            }
            if (isset($args['rating'])) {
                $query->whereIN('rating', $args['rating']);
            }
        };
        return $reviews->where($where)->orderBy('reviews.id', 'desc')->get();
    }

}