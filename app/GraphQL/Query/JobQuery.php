<?php

namespace App\GraphQL\Query;

use App\Models\Job;
use GraphQL;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Folklore\GraphQL\Support\Query;


class JobQuery extends Query
{
    protected $attributes = [
        'name' => 'job'
    ];

    public function type()
    {
        return GraphQL::type('Job');
    }

    public function args()
    {
        return [
            'id' => ['name' => 'id', 'type' => Type::int()],
            'customer_id' => ['name' => 'customer_id', 'type' => Type::int()],
            'token' => ['name' => 'token', 'type' => Type::string()],
        ];
    }

    public function resolve($root, $args, $context, ResolveInfo $info)
    {
        if (!isset($args['id']) || !isset($args['token']) || !isset($args['customer_id'])) {
            return null;
        }
        $job = Job::find($args['id']);
        if ($args['customer_id'] !== $job->partnerOrder->order->customer_id) {
            return null;
        }
        return $job;
    }
}