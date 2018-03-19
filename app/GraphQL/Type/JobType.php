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
            'price' => ['type' => Type::float()],
            'schedule_date' => ['type' => Type::string()],
            'preferred_time' => ['type' => Type::string()],
            'completed_at_timestamp' => ['type' => Type::float()],
            'category' => ['type' => GraphQL::type('Category')],
            'review' => ['type' => GraphQL::type('Review')],
            'resource' => ['type' => GraphQL::type('Resource')],
            'services' => ['type' => Type::listOf(GraphQL::type('JobService'))],
            'materials' => ['type' => Type::listOf(GraphQL::type('JobMaterial'))],
            'order' => ['type' => GraphQL::type('Order')]
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

    protected function resolveServicesField($root, $args)
    {
        if (count($root->jobServices) == 0) {
            return array(array(
                'name' => $root->service_name, 'options' => $root->service_variables,
                'quantity' => (float)$root->service_quantity, 'unit_price' => (float)$root->service_unit_price)
            );
        } else {
            $services = [];
            foreach ($root->jobServices as $jobService) {
                array_push($services, array(
                        'name' => $jobService->service->name, 'options' => $jobService->variables,
                        'quantity' => (float)$jobService->quantity, 'unit_price' => (float)$jobService->unit_price)
                );
            }
            return $services;
        }
    }

    protected function resolveMaterialsField($root, $args)
    {
        return $root->usedMaterials;
    }

    protected function resolveOrderField($root, $args)
    {
        return $root->partnerOrder;
    }

    protected function resolvePriceField($root, $args)
    {
        if (isset($job['totalPrice'])) {
            $root->calculate(true);
        }
        return (double)$root->totalPrice;
    }

}