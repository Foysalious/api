<?php namespace App\Transformers\Business;

use League\Fractal\TransformerAbstract;
use Sheba\Business\Bid\StatusCalculator;
use App\Transformers\AttachmentTransformer;

class BidDetailsTransformer extends TransformerAbstract
{
    protected $defaultIncludes = ['attachments'];

    public function transform($bid)
    {
        $price_quotation = $bid->items->where('type', 'price_quotation')->first();
        $technical_evaluation = $bid->items->where('type', 'technical_evaluation')->first();
        $company_evaluation = $bid->items->where('type', 'company_evaluation')->first();
        return [
            'id' => $bid->id,
            'status' => StatusCalculator::resolveStatus($bid),
            'price' => $bid->price,
            'title' => $bid->procurement->title,
            'type' => $bid->procurement->type,
            'is_awarded' => $bid->canNotSendHireRequest(),
            'terms' => $bid->terms,
            'policies' => $bid->policies,
            'proposal' => $bid->proposal,
            'start_date' => $bid->procurement->procurement_start_date->format('d/m/y'),
            'end_date' => $bid->procurement->procurement_end_date->format('d/m/y'),
            'created_at' => $bid->created_at->format('d/m/y'),
            'price_quotation' => $price_quotation ? $price_quotation->fields ? $price_quotation->fields->toArray() : null : null,
            'technical_evaluation' => $technical_evaluation ? $technical_evaluation->fields ? $technical_evaluation->fields->toArray() : null : null,
            'company_evaluation' => $company_evaluation ? $company_evaluation->fields ? $company_evaluation->fields->toArray() : null : null,
            'vendor' => [
                'name' => $bid->bidder->name,
                'logo' => $bid->bidder->logo,
                'domain' => $bid->bidder->sub_domain,
                'rating' => round($bid->bidder->reviews->avg('rating'), 2),
                'total_rating' => $bid->bidder->reviews->count()
            ],
        ];
    }

    public function includeAttachments($bid)
    {
        $collection = $this->collection($bid->attachments, new AttachmentTransformer());
        return $collection->getData() ? $collection : $this->item(null, function () {
            return [];
        });
    }
}