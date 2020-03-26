<?php namespace Sheba\Business\Procurement;

use App\Models\Bid;
use App\Models\Business;
use App\Models\Procurement;
use Sheba\Repositories\Interfaces\ProcurementRepositoryInterface;

class WorkOrderDataGenerator
{
    /** @var Procurement $procurement */
    private $procurement;
    /** @var Bid $bid */
    private $bid;
    private $procurementRepo;
    /** @var Business $business */
    private $business;

    public function __construct(ProcurementRepositoryInterface $procurement_repo)
    {
        $this->procurementRepo = $procurement_repo;
    }

    /**
     * @param $procurement
     * @return $this
     */
    public function setProcurement($procurement)
    {
        $this->procurement = $this->procurementRepo->find((int)$procurement);
        $this->procurement->calculate();
        return $this;
    }

    /**
     * @param Bid $bid
     * @return $this
     */
    public function setBid(Bid $bid)
    {
        $this->bid = $bid;
        return $this;
    }

    public function get()
    {
        $items = [];
        if ($this->procurement->isAdvanced())
            $items = $this->generateBidItemData();

        return [
            'code' => $this->procurement->workOrderCode(),
            'from' => [
                'name' => $this->business->name,
                'address' => $this->business->address,
                'mobile' => $this->business->getContactPerson()
            ],
            'to' => [
                'name' => $this->bid->bidder->name,
                'mobile' => $this->bid->bidder->getContactPerson(),
                'address' => $this->bid->bidder->address
            ],
            'items' => $items,
            'terms' => $this->bid->terms,
            "sub_total" => $this->procurement->totalPrice,
            "due" => $this->procurement->due,
            "grand_total" => $this->procurement->totalPrice
        ];
    }

    public function setBusiness(Business $business)
    {
        $this->business = $business;
        return $this;
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
                'total_price' => $field->result
            ]);
        }

        return $item_fields;
    }
}
