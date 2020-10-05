<?php namespace Sheba\Business\Procurement;

use App\Models\Bid;
use App\Models\Business;
use App\Models\Procurement;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;
use Sheba\Reports\PdfHandler;
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
    /** @var PdfHandler $pdfHandler */
    private $pdfHandler;

    public function __construct(ProcurementRepositoryInterface $procurement_repo, PdfHandler $pdf_handler)
    {
        $this->procurementRepo = $procurement_repo;
        $this->pdfHandler = $pdf_handler;
    }

    /**
     * @param $procurement
     * @return $this
     */
    public function setProcurement($procurement)
    {
        $this->procurement = $procurement instanceof Procurement ? $procurement : $this->procurementRepo->find((int)$procurement);
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
                'logo' => $this->business->logo,
                'mobile' => $this->business->getContactNumber()
            ],
            'to' => [
                'name' => $this->bid->bidder->name,
                'address' => $this->bid->bidder->address,
                'logo' => $this->bid->bidder->logo,
                'mobile' => $this->bid->bidder->getContactNumber()
            ],
            'items' => $items,
            'terms' => $this->bid->terms,
            "sub_total" => $this->procurement->totalPrice,
            "due" => $this->procurement->due,
            "grand_total" => $this->procurement->totalPrice,
            "tk_sign" => "https://cdn-shebaxyz.s3.ap-south-1.amazonaws.com/icons/taka.png"
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

    public function storeInCloud()
    {
        $work_order = ['work_order' => $this->get()];
        return $this->pdfHandler->setData($work_order)
            ->setName($this->procurement->id)
            ->setFolder('tender-work-order/file/')
            ->setViewPath('pdfs.')
            ->setViewFile('work_order')
            ->save();
    }
}
