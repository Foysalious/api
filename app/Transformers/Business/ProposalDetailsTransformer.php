<?php namespace App\Transformers\Business;

use App\Models\Bid;
use App\Models\Procurement;
use League\Fractal\TransformerAbstract;

class ProposalDetailsTransformer extends TransformerAbstract
{
    /** @var Bid $bid */
    private $bid;
    /** @var Procurement $procurement */
    private $procurement;

    public function __construct(Bid $bid)
    {
        $this->bid = $bid;
    }

    public function transform(Procurement $procurement)
    {
        $this->procurement = $procurement;
        $procurement_price_quotations = $this->generateProcurementItemData('price_quotation');
        $bid_price_quotations = $this->generateBidItemData('price_quotation');

        return [
            'procurement_id' => $this->procurement->id,
            'title' => $this->procurement->title,
            'company_name' => $this->procurement->owner ? $this->procurement->owner->name : 'N/A',
            'start_date' => $this->procurement->procurement_start_date->format('d/m/y'),
            'end_date' => $this->procurement->procurement_end_date->format('d/m/y'),
            'payment_options' => $this->procurement->payment_options,
            'bid_id' => $this->bid->id,
            'bid_price' => $this->bid->price,
            'procurement_price_quotations' => $procurement_price_quotations ? $procurement_price_quotations->toArray() : null,
            'bid_price_quotations' => $bid_price_quotations ? $bid_price_quotations->toArray() : null ,
            'bid_terms' => $this->bid->terms,
            'bid_policies' => $this->bid->policies
        ];
    }

    private function generateProcurementItemData($type)
    {
        $type_data = $this->procurement->items->where('type', $type)->first();
        $total_proposed_price = 0;
        return $type_data ? $type_data->fields->map(function ($field) use (&$total_proposed_price) {
            $unit = $field->variables ? json_decode($field->variables)->unit ? json_decode($field->variables)->unit : 0 : 0;
            $total_price = $unit * $field->result;
            $total_proposed_price += $total_price;
            return [
                'id' => $field->id,
                'title' => $field->title,
                'short_description' => $field->short_description,
                'unit' => $unit,
                'result' => $field->result,
                'total_price' => $total_price,
                'total_proposed_price' => $total_proposed_price
            ];
        }) : null;
    }

    private function generateBidItemData($type)
    {
        $type_data = $this->bid->items->where('type', $type)->first();
        $total_proposed_price = 0;
        return $type_data ? $type_data->fields->map(function ($field) use (&$total_proposed_price) {
            $unit = $field->variables ? json_decode($field->variables)->unit ? json_decode($field->variables)->unit : 0 : 0;
            $total_price = $field->result;
            $total_proposed_price += $total_price;
            return [
                'id' => $field->id,
                'title' => $field->title,
                'short_description' => $field->short_description,
                'unit' => $unit,
                'result' => $field->result,
                'total_price' => $total_price,
                'total_proposed_price' => $this->bid->price
            ];
        }) : null;
    }
}
