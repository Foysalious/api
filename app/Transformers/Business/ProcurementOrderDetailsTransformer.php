<?php namespace App\Transformers\Business;

use App\Models\Bid;
use League\Fractal\TransformerAbstract;
use Sheba\Business\Procurement\OrderStatusCalculator;

class ProcurementOrderDetailsTransformer extends TransformerAbstract
{
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
        if ($procurement->isAdvanced())
            $bid_price_quotations = $this->generateBidItemData();

        return [
            'procurement_id' => $procurement->id,
            'procurement_title' => $procurement->title ? $procurement->title : substr($procurement->long_description, 0, 20),
            'procurement_status' => OrderStatusCalculator::resolveStatus($procurement),
            'procurement_start_date' => $procurement->procurement_start_date->format('d/m/y'),
            'procurement_end_date' =>$procurement->procurement_end_date->format('d/m/y'),
            'procurement_type' => $procurement->type,
            'procurement_additional_info' => $procurement->long_description,
            'vendor' => [
                'name' => $this->bid->bidder->name,
                'logo' => $this->bid->bidder->logo,
                'contact_person' => $this->bid->bidder->getContactPerson(),
                'mobile' => $this->bid->bidder->getMobile(),
                'address' => $this->bid->bidder->address,
                'rating' => round($this->bid->bidder->reviews->avg('rating'), 2),
                'total_rating' => $this->bid->bidder->reviews->count()
            ],
            'bid_id' => $this->bid->id,
            'bid_price' => $this->bid->price,
            'bid_price_quotations' => $bid_price_quotations
        ];
    }

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
                'unit_price' => number_format($field->result / $unit, 2),
                'total_price' => $field->result,
            ]);
        }
        return $item_fields;
    }
}