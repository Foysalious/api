<?php namespace App\Transformers\Business;

use App\Models\Bid;
use App\Transformers\AttachmentTransformer;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use League\Fractal\TransformerAbstract;
use Sheba\Business\Procurement\OrderStatusCalculator;

class ProcurementOrderDetailsTransformer extends TransformerAbstract
{
    protected $defaultIncludes = ['attachments'];

    const IS_VERIFIED = 'Verified';
    const VERIFIED = 'Sheba Verified';
    const UNVERIFIED = 'Unverified';

    /** @var Bid $bid */
    private $bid;

    public function __construct(Bid $bid)
    {
        $this->bid = $bid;
    }

    /**
     * @param $procurement
     * @return array
     */
    public function transform($procurement)
    {
        $bid_price_quotations = null;
        if ($procurement->isAdvanced()) $bid_price_quotations = $this->generateBidItemData();

        $category = $procurement->category ? $procurement->category : null;
        $bidder = $this->bid->bidder;

        return [
            'procurement_id' => $procurement->id,
            'procurement_code' => $procurement->orderCode(),
            'procurement_title' => $procurement->title ? $procurement->title : substr($procurement->long_description, 0, 15),
            'procurement_status' => OrderStatusCalculator::resolveStatus($procurement),
            'procurement_start_date' => $procurement->procurement_start_date->format('d/m/y'),
            'procurement_end_date' => $procurement->procurement_end_date->format('d/m/y'),
            'procurement_type' => $procurement->type,
            'procurement_additional_info' => $procurement->long_description,
            'category' => $category ? [
                'id' => $category->id,
                'name' => $category->name,
                'image' => $category->thumb,
            ] : null,
            'vendor' => [
                'name' => $bidder->name,
                'logo' => $bidder->logo,
                'contact_person' => $bidder->getContactPerson(),
                'mobile' => $bidder->getMobile(),
                'status' => $bidder->status === self::IS_VERIFIED ? self::VERIFIED: self::UNVERIFIED,
                'address' => $bidder->address,
                'rating' => round($bidder->reviews->avg('rating'), 2),
                'total_rating' => $bidder->reviews->count()
            ],
            'bid_id' => $this->bid->id,
            'bid_price' => $this->bid->price,
            'bid_price_quotations' => $bid_price_quotations
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

    /**
     * @return array
     */
    private function generateBidItemData()
    {
        $item_type = $this->bid->items->where('type', 'price_quotation')->first();
        $item_fields = [];
        foreach ($item_type->fields as $field) {
            $unit = $field->variables ? json_decode($field->variables)->unit ? json_decode($field->variables)->unit : 0 : 0;
            array_push($item_fields, [
                'id' => $field->id,
                'title' => $field->title,
                'short_description' => $field->short_description,
                'unit' => $unit,
                'unit_price' => $unit ? number_format($field->result / $unit, 2) : 0,
                'total_price' => $field->result,
            ]);
        }

        return $item_fields;
    }
}
