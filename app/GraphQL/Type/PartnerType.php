<?php

namespace App\GraphQL\Type;

use Carbon\Carbon;
use GraphQL;
use \Folklore\GraphQL\Support\Type as GraphQlType;
use GraphQL\Type\Definition\Type;
use Redis;

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
        return $root->jobs->where('status', 'Served')->count();
    }

    protected function resolveAvgRatingField($root, $args)
    {
        return round($root->reviews->avg('rating'), 2);
    }

    protected function resolveTotalResourcesField($root, $args)
    {
        return $root->resources->count();
    }
}