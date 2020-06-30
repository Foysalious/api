<?php namespace App\Transformers\Business;

use App\Models\Bid;
use App\Models\BidItem;
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
        $price_quotations = $this->generateItemDataBy();
        /*$total_proposed_budget = $price_quotations ? $price_quotations->sum('proposed_budget') : 0.00;
        $total_final_budget = $price_quotations ? $price_quotations->sum('final_budget') : 0.00;*/

        /** @var Business $business */
        $business = $this->procurement->owner;
        /** @var Partner $partner */
        $partner = $this->bid->bidder;
        $start_date = $this->procurement->procurement_start_date->format('d/m/y');
        $end_date = $this->procurement->procurement_end_date->format('d/m/y');
        $proposed_budget = $this->bid->bidder_price ?: $this->bid->price;

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
            'proposal' => $this->bid->proposal,
            'final_budget' => (double)$this->bid->price,
            'proposed_budget' => (double)$proposed_budget,
            'bid_id' => $this->bid->id,
            'bid_terms' => $this->bid->terms,
            'bid_policies' => $this->bid->policies,
            'price_quotations' => $price_quotations,
            // 'total_proposed_budget' => (double)$total_proposed_budget,
            // 'total_final_budget' => (double)$total_final_budget
        ];
    }

    /**
     * @return array|null
     */
    private function generateItemDataBy()
    {
        /** @var BidItem $bid_items */
        $bid_items = $this->bid->items->where('type', 'price_quotation')->first();
        if (!$bid_items) return null;

        return $bid_items->fields->map(function ($field) use (&$total_proposed_price) {
            $unit = $field->variables ? json_decode($field->variables)->unit ? json_decode($field->variables)->unit : 0 : 0;

            return [
                'id'                => $field->id,
                'title'             => $field->title,
                'short_description' => $field->short_description,
                'unit'              => $unit,
                'proposed_budget'   => (double)$field->bidder_result,
                'final_budget'      => (double)$field->result
            ];
        });
    }
}
