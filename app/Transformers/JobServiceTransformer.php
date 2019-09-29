<?php

namespace App\Transformers;


use League\Fractal\TransformerAbstract;

class JobServiceTransformer extends TransformerAbstract
{
    protected $defaultIncludes = [
        'questions'
    ];

    public function transform($service)
    {
        return [
            'id' => (int)$service->service_id,
            'name' => $service->name,
            'unit' => $service->unit,
            'quantity' => $service->quantity,
            'option' => $service->option,
            'type' => $service->variable_type,
            'picture' => $service->thumb,
        ];
    }

    public function includeQuestions($service)
    {
        $collection = $this->collection($service->variables, new QuestionTransformer());
        return $collection->getData() ? $collection : $this->item(null, function () {
            return [];
        });
    }
}