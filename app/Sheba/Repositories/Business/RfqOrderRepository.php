<?php namespace Sheba\Repositories\Business;

use App\Models\Bid;
use App\Models\Partner;
use App\Models\Procurement;
use Carbon\Carbon;
use Sheba\Repositories\BaseRepository;
use Sheba\Repositories\Interfaces\RfqOrderRepositoryInterface;

class RfqOrderRepository extends BaseRepository implements RfqOrderRepositoryInterface
{
    private $procurement;
    private $partner;
    private $bid;

    public function setProcurement(Procurement $procurement)
    {
        $this->procurement = $procurement;
        return $this;
    }

    public function setPartner(Partner $partner)
    {
        $this->partner = $partner;
        return $this;
    }

    public function setBid(Bid $bid)
    {
        $this->bid = $bid;
        return $this;
    }

    public function formatData()
    {
        $bid_price_quotations = $this->generateBidItemData();
        $order_details = [
            'procurement_id' => $this->procurement->id,
            'procurement_title' => $this->procurement->title,
            'procurement_start_date' => Carbon::parse($this->procurement->procurement_start_date)->format('d/m/y'),
            'procurement_end_date' => Carbon::parse($this->procurement->procurement_end_date)->format('d/m/y'),
            'procurement_type' => $this->procurement->type,
            'procurement_additional_info' => $this->procurement->long_description,

            'bid_id' => $this->bid->id,
            'bid_price' => $this->bid->price,#Total Proposed Price
            'bid_price_quotations' => $bid_price_quotations
        ];
        return $order_details;
    }

    private function generateBidItemData()
    {
        $item_type = $this->bid->items->where('type', 'price_quotation')->first();
        $item_fields = [];
        foreach ($item_type->fields as $field){
            $unit = $field->variables ? json_decode($field->variables)->unit ? json_decode($field->variables)->unit : 0 : 0;
            array_push( $item_fields,[
                'id' => $field->id,
                'title' => $field->title,
                'short_description' => $field->short_description,
                'unit' => $unit,
                'unit_price' => number_format($field->result/$unit, 2),
                'total_price' => $field->result,
            ]);
        }
        return $item_fields;
    }
}