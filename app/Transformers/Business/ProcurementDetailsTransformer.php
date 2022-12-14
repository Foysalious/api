<?php namespace App\Transformers\Business;

use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use League\Fractal\TransformerAbstract;
use App\Transformers\AttachmentTransformer;
use Sheba\Business\Procurement\StatusCalculator;
use Sheba\Dal\Procurement\PublicationStatuses;

class ProcurementDetailsTransformer extends TransformerAbstract
{
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
            'title' => $procurement->title ? $procurement->title : null,
            'status' => StatusCalculator::resolveStatus($procurement),
            'category' => $procurement->category_id ? [ 'id' => $procurement->category_id, 'name' => $procurement->category->name ] : null,
            'type' => $procurement->type,
            'long_description' => $procurement->long_description,
            'labels' => $procurement->getTagNamesAttribute()->toArray(),
            'start_date' => $procurement->procurement_start_date->format('d/m/Y'),
            'end_date' => $procurement->procurement_end_date->format('d/m/Y'),
            'last_date_of_submission' => $procurement->last_date_of_submission->format('d/m/Y'),
            'estimated_price' => (int) $procurement->estimated_price,
            'published_at' => ($procurement->publication_status == PublicationStatuses::PUBLISHED) ? $procurement->published_at->format('d/m/y') : null,
            'number_of_participants' => $procurement->number_of_participants,
            'payment_options' => $procurement->payment_options,
            'created_at' => $procurement->created_at->format('d/m/y'),
            'price_quotation' => $price_quotation ? $price_quotation->fields ? $price_quotation->fields->toArray() : null : null,
            'technical_evaluation' => $technical_evaluation ? $technical_evaluation->fields ? $technical_evaluation->fields->toArray() : null : null,
            'company_evaluation' => $company_evaluation ? $company_evaluation->fields ? $company_evaluation->fields->toArray() : null : null,
        ];
    }

    /**
     * @param $procurement
     * @return Collection|Item
     */
    public function includeAttachments($procurement)
    {
        $collection = $this->collection($procurement->attachments, new AttachmentTransformer());
        return $collection->getData() ? $collection : $this->item(null, function () {
            return [];
        });
    }
}
