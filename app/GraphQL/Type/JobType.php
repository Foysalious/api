<?php

namespace App\GraphQL\Type;

use GraphQL;
use \Folklore\GraphQL\Support\Type as GraphQlType;
use GraphQL\Type\Definition\Type;

class JobType extends GraphQlType
{
    protected $attributes = [
        'name' => 'Job'
    ];

    public function fields()
    {
        return [
            'id' => ['type' => Type::int()],
            'completed_at' => ['type' => Type::string()],
            'additional_information' => ['type' => Type::string()],
            'schedule_date' => ['type' => Type::string()],
            'preferred_time' => ['type' => Type::string()],
            'completed_at_timestamp' => ['type' => Type::float()],
            'category' => ['type' => GraphQL::type('Category')],
            'review' => ['type' => GraphQL::type('Review')],
            'resource' => ['type' => GraphQL::type('Resource')],
        ];
    }

    protected function resolveCompletedAtField($root, $args)
    {
        return $root->delivered_date ? $root->delivered_date->format('M jS,Y') : null;
    }

    protected function resolveCompletedAtTimestampField($root, $args)
    {
        return $root->delivered_date ? $root->delivered_date->timestamp : null;
    }
}