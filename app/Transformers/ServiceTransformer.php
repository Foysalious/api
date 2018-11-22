<?php

namespace App\Transformers;

use League\Fractal\TransformerAbstract;

class ServiceTransformer extends TransformerAbstract
{
    protected $defaultIncludes = [
        'questions'
    ];

    public function transform($service)
    {
        return [
            'id' => (int)$service->id,
            'name' => $service->name,
            'picture' => $service->thumb,
            'description' => $service->description,
            'faqs' => $service->faqs,
            'type' => $service->variable_type,
        ];
    }

    public function includeQuestions($service)
    {
        $collection = $this->collection($service->questions, new QuestionTransformer());
        return $collection->getData() ? $collection : $this->item(null, function () {
            return [];
        });
    }
}