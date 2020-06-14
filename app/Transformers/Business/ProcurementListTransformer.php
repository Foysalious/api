<?php namespace App\Transformers\Business;

use League\Fractal\TransformerAbstract;

class ProcurementListTransformer extends TransformerAbstract
{
    const IS_DRAFTED = 0;
    const DRAFTED = 'drafted';
    const IS_PUBLISHED = 1;
    const PUBLISHED = 'published';
    const PENDING = 'pending';

    /**
     * @param $procurement
     * @return array
     */
    public function transform($procurement)
    {
        $title = $procurement->title ? $procurement->title : substr($procurement->long_description, 0, 20);
        $status = $this->getStatus($procurement);
        return [
            "id" => $procurement->id,
            "title" => $title,
            "status" => $status,
            "created_at" => $procurement->created_at->format('d/m/y'),
            "last_date_of_submission" => $procurement->last_date_of_submission->format('d/m/y'),
            "bid_count" => $procurement->bids()->where('status', '<>', 'pending')->get()->count()
        ];
    }

    private function getStatus($procurement)
    {
        if ($procurement->is_published == self::IS_DRAFTED) return self::DRAFTED;
        if ($procurement->is_published == self::IS_PUBLISHED && $procurement->status == self::PENDING) return self::PUBLISHED;
        return $procurement->status;
    }
}