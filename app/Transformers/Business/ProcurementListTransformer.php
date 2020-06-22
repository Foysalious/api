<?php namespace App\Transformers\Business;

use League\Fractal\TransformerAbstract;
use Sheba\Business\Procurement\StatusCalculator;

class ProcurementListTransformer extends TransformerAbstract
{

    /**
     * @param $procurement
     * @return array
     */
    public function transform($procurement)
    {
        return [
            "id" => $procurement->id,
            "title" => $procurement->title ? $procurement->title : substr($procurement->long_description, 0, 20),
            'status' => StatusCalculator::resolveStatus($procurement),
            "created_at" => $procurement->created_at->format('d/m/y'),
            "last_date_of_submission" => $procurement->last_date_of_submission->format('d/m/y'),
            "bid_count" => $procurement->bids()->where('status', '<>', 'pending')->get()->count()
        ];
    }
}