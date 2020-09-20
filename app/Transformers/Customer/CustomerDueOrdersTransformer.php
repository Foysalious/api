<?php namespace App\Transformers\Customer;

use League\Fractal\TransformerAbstract;

class CustomerDueOrdersTransformer extends TransformerAbstract
{
    public function transform($due_order) {
        $due_order->calculate(true);
        return [
            'id' => $due_order->id,
            'order_id' => $due_order->order_id,
            'job_id' => $due_order->lastJob()->id,
            'due' => (double)$due_order->dueWithLogistic,
            'text' => 'Dear customer, You have a due on ' . $due_order->lastJob()->category->name . ', please clear the payment due'
        ];
    }
}