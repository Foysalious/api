<?php namespace App\Transformers\Business;

use App\Models\Bid;
use App\Models\Business;
use App\Models\Partner;
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
        $procurement_price_quotations = $this->generateItemDataBy($this->procurement);
        $bid_price_quotations = $this->generateItemDataBy($this->bid);

        /** @var Business $business */
        $business = $this->procurement->owner;
        /** @var Partner $partner */
        $partner = $this->bid->bidder;
        $start_date = $this->procurement->procurement_start_date->format('d/m/y');
        $end_date = $this->procurement->procurement_end_date->format('d/m/y');

        return [
            'procurement_id' => $this->procurement->id,
            'title' => $this->procurement->title,
            'description' => $this->procurement->long_description,
            'company' => [
                'name' => $business->name,
                'logo' => $business->logo
            ],
            'vendor' => [
                'name' => $partner->name,
                'logo' => $partner->logo
            ],
            'delivery_within' => [
                'date_range' => $start_date . ' - ' . $end_date,
                'icon' => config('sheba.s3_url') . 'business_assets/tender/icons/png/time.png',
            ],
            'payment_options' => $this->procurement->payment_options,
            'bid_id' => $this->bid->id,
            'bid_price' => $this->bid->price,
            'procurement_price_quotations' => $procurement_price_quotations ? $procurement_price_quotations->toArray() : null,
            'bid_price_quotations' => $bid_price_quotations ? $bid_price_quotations->toArray() : null ,
            'bid_terms' => $this->bid->terms,
            'bid_policies' => $this->bid->policies
        ];
    }

    /**
     * @param Procurement $type | Bid $type
     * @return array|null
     */
    private function generateItemDataBy($type)
    {
        $type_data = $type->items->where('type', 'price_quotation')->first();
        $total_proposed_price = 0;
        return $type_data ? $type_data->fields->map(function ($field) use (&$total_proposed_price, $type) {
            $unit = $field->variables ? json_decode($field->variables)->unit ? json_decode($field->variables)->unit : 0 : 0;
            $total_price = ($type instanceof Procurement) ? ($unit * $field->result) : $field->result;
            $total_proposed_price += $total_price;
            $total_proposed_price = ($type instanceof Procurement) ? $total_proposed_price : $this->bid->price;

            return [
                'id'                    => $field->id,
                'title'                 => $field->title,
                'short_description'     => $field->short_description,
                'unit'                  => $unit,
                'result'                => $field->result,
                'total_price'           => (double)$total_price,
                'total_proposed_price'  => (double)$total_proposed_price
            ];
        }) : null;
    }
}
