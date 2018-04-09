<?php

namespace App\GraphQL\Type;

use Carbon\Carbon;
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
            'status' => ['type' => Type::string()],
            'schedule_date' => ['type' => Type::string()],
            'schedule_date_timestamp' => ['type' => Type::int()],
            'preferred_time' => ['type' => Type::string()],
            'preferred_time_readable' => ['type' => Type::string()],
            'completed_at_timestamp' => ['type' => Type::float()],
            'category' => ['type' => GraphQL::type('Category')],
            'review' => ['type' => GraphQL::type('Review')],
            'resource' => ['type' => GraphQL::type('Resource')],
            'services' => ['type' => Type::listOf(GraphQL::type('JobService'))],
            'materials' => ['type' => Type::listOf(GraphQL::type('JobMaterial'))],
            'order' => ['type' => GraphQL::type('Order')],
            'complains' => ['type' => Type::listOf(GraphQL::type('Complain'))],
            'hasComplain' => ['type' => Type::int()],
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
                'id' => $root->service->id,
                'name' => $root->service_name, 'options' => $root->service_variables,
                'unit' => $root->service->unit,
                'quantity' => (float)$root->service_quantity, 'unit_price' => (float)$root->service_unit_price),
                'option' => $root->service_option
            );
        } else {
            $services = [];
            foreach ($root->jobServices as $jobService) {
                array_push($services, array(
                    'id' => $jobService->service->id,
                    'name' => $jobService->service->name,
                    'options' => $jobService->variables,
                    'option'=>$jobService->option,
                    'unit' => $jobService->service->unit,
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
        $partnerOrder = $root->partnerOrder;
        $partnerOrder->calculate(true);
        return (double)$partnerOrder->totalPrice;
    }

    protected function resolveComplainsField($root, $args, $fields)
    {
        return $root->complains->where('accessor_id', 1);
    }

    protected function resolveHasComplainField($root, $args, $fields)
    {
        return $root->complains->count() > 0 ? 1 : 0;
    }

    protected function resolvePreferredTimeReadableField($root, $args)
    {
        return $root->readable_preferred_time;
    }

    protected function resolveScheduleDateTimestampField($root, $args)
    {
        return Carbon::parse($root->schedule_date)->timestamp;
    }


}