<?php

namespace App\GraphQL\Type;

use Carbon\Carbon;
use GraphQL;
use \Folklore\GraphQL\Support\Type as GraphQlType;
use GraphQL\Type\Definition\Type;
use Illuminate\Support\Facades\Redis;

class PartnerType extends GraphQlType
{
    protected $attributes = [
        'name' => 'Partner',
        'description' => 'Sheba Partner'
    ];

    public function fields()
    {
        return [
            'id' => ['type' => Type::int()],
            'name' => ['type' => Type::string()],
            'logo' => ['type' => Type::string()],
            'slug' => ['type' => Type::string()],
            'locations' => ['type' => Type::listOf(GraphQL::type('Location'))],
            'total_jobs' => ['type' => Type::int()],
            'total_completed_orders' => ['type' => Type::int(), 'description' => 'Total served jobs of Category'],
            'total_resources' => ['type' => Type::int()],
            'avg_rating' => ['type' => Type::float()],
        ];
    }

    protected function resolveSlugField($root, $args)
    {
        return $root->sub_domain;
    }

    protected function resolveTotalJobsField($root, $args)
    {
        return $root->jobs->first() ? $root->jobs->first()->total_jobs : 0;
    }

    protected function resolveTotalCompletedOrdersField($root, $args)
    {
        return $root->jobs->first() ? $root->jobs->first()->total_completed_orders : 0;
    }

    protected function resolveAvgRatingField($root, $args)
    {
        return $root->reviews->first() ? round($root->reviews->first()->avg_rating, 2) : 0;
    }

    protected function resolveTotalResourcesField($root, $args)
    {
        return $root->resources->first() ? $root->resources->first()->total_resources : 0;
    }
}