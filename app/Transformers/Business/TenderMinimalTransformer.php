<?php namespace App\Transformers\Business;

use League\Fractal\TransformerAbstract;

class TenderMinimalTransformer extends TransformerAbstract
{
    /**
     * @param $procurement
     * @return array
     */
    public function transform($procurement)
    {
        return [
            'id' => $procurement->id,
            'title' => $procurement->title,
            'description' => $procurement->long_description,
            'estimated_price' => $procurement->estimated_price ? (double)$procurement->estimated_price : null,
            'created_at' => 'Posted ' . $procurement->created_at->diffForHumans()
        ];
    }
}
