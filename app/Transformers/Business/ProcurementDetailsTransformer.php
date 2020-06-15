<?php namespace App\Transformers\Business;

use League\Fractal\TransformerAbstract;
use App\Transformers\AttachmentTransformer;

class ProcurementDetailsTransformer extends TransformerAbstract
{
    const IS_DRAFTED = 0;
    const DRAFTED = 'drafted';
    const IS_PUBLISHED = 1;
    const PUBLISHED = 'published';
    const PENDING = 'pending';

    protected $defaultIncludes = ['attachments'];

    /**
     * @param $procurement
     * @return array
     */
    public function transform($procurement)
    {
        $price_quotation = $procurement->items->where('type', 'price_quotation')->first();
        $technical_evaluation = $procurement->items->where('type', 'technical_evaluation')->first();
        $company_evaluation = $procurement->items->where('type', 'company_evaluation')->first();

        return [
            'id' => $procurement->id,
            'title' => $procurement->title ? $procurement->title : substr($procurement->long_description, 0, 20),
            'status' => $this->getStatus($procurement),
            'long_description' => $procurement->long_description,
            'labels' => $procurement->getTagNamesAttribute()->toArray(),
            'start_date' => $procurement->procurement_start_date->format('d/m/y'),
            'published_at' => $procurement->is_published ? $procurement->published_at->format('d/m/y') : null,
            'end_date' => $procurement->procurement_end_date->format('d/m/y'),
            'number_of_participants' => $procurement->number_of_participants,
            'last_date_of_submission' => $procurement->last_date_of_submission->format('d/m/y'),
            'payment_options' => $procurement->payment_options,
            'created_at' => $procurement->created_at->format('d/m/y'),
            'price_quotation' => $price_quotation ? $price_quotation->fields ? $price_quotation->fields->toArray() : null : null,
            'technical_evaluation' => $technical_evaluation ? $technical_evaluation->fields ? $technical_evaluation->fields->toArray() : null : null,
            'company_evaluation' => $company_evaluation ? $company_evaluation->fields ? $company_evaluation->fields->toArray() : null : null,
        ];
    }

    /**
     * @param $procurement
     * @return \League\Fractal\Resource\Collection|\League\Fractal\Resource\Item
     */
    public function includeAttachments($procurement)
    {
        $collection = $this->collection($procurement->attachments, new AttachmentTransformer());
        return $collection->getData() ? $collection : $this->item(null, function () {
            return [];
        });
    }

    /**
     * @param $procurement
     * @return string
     */
    private function getStatus($procurement)
    {
        if ($procurement->is_published == self::IS_DRAFTED) return self::DRAFTED;
        if ($procurement->is_published == self::IS_PUBLISHED && $procurement->status == self::PENDING) return self::PUBLISHED;
        return $procurement->status;
    }
}