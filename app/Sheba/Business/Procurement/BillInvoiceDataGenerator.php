<?php namespace Sheba\Business\Procurement;

use App\Models\Bid;
use App\Models\Business;
use App\Models\Procurement;
use NumberFormatter;
use Sheba\Business\ProcurementPaymentRequest\Status;
use Sheba\Dal\ProcurementPaymentRequest\Model as ProcurementPaymentRequest;
use Sheba\Dal\ProcurementPaymentRequest\ProcurementPaymentRequestRepositoryInterface;
use Sheba\Repositories\Interfaces\ProcurementRepositoryInterface;

class BillInvoiceDataGenerator
{
    /** @var Procurement $procurement */
    private $procurement;
    /** @var Bid $bid */
    private $bid;
    private $procurementRepo;
    /** @var Business $business */
    private $business;
    /** @var ProcurementPaymentRequestRepositoryInterface $procurementPaymentRequestRepo */
    private $procurementPaymentRequestRepo;
    /** @var ProcurementPaymentRequest $paymentRequest */
    private $paymentRequest;

    const BILL = 'bill';
    const INVOICE = 'invoice';

    /**
     * BillInvoiceDataGenerator constructor.
     * @param ProcurementRepositoryInterface $procurement_repo
     * @param ProcurementPaymentRequestRepositoryInterface $procurement_payment_request_repo
     */
    public function __construct(ProcurementRepositoryInterface $procurement_repo,
                                ProcurementPaymentRequestRepositoryInterface $procurement_payment_request_repo)
    {
        $this->procurementRepo = $procurement_repo;
        $this->procurementPaymentRequestRepo = $procurement_payment_request_repo;
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

    public function setBusiness(Business $business)
    {
        $this->business = $business;
        return $this;
    }

    public function setPaymentRequest($payment_request)
    {
        $this->paymentRequest = $this->procurementPaymentRequestRepo->find($payment_request);
        return $this;
    }

    public function get()
    {
        $items = [];
        if ($this->procurement->isAdvanced())
            $items = $this->generateBidItemData();

        $data = [
            'type' => $this->getType(),
            'submitted_date' => $this->paymentRequest ? $this->paymentRequest->created_at->format('d M, Y') : null,
            'from' => [
                'name' => $this->business->name,
                'address' => $this->business->address,
                'logo' => $this->business->logo,
                'mobile' => $this->business->getContactNumber()
            ],
            'to' => [
                'name' => $this->bid->bidder->name,
                'address' => $this->bid->bidder->address,
                'mobile' => $this->bid->bidder->getContactNumber()
            ],
            'items' => $items,
            'terms' => $this->bid->terms,
            'sub_total' => (double)$this->procurement->totalPrice,
            'grand_total' => (double)$this->procurement->totalPrice,
            'due' => (double)$this->procurement->due,
            'tk_sign' => "https://cdn-shebaxyz.s3.ap-south-1.amazonaws.com/icons/taka.png",
            'terms_and_conditions' => $this->bid->terms,
            'is_for_payment_request' => $this->paymentRequest ? 1 : 0
        ];

        if ($data['type'] == self::INVOICE) $data += [
            'code' => $this->procurement->invoiceCode(),
            'paid' => (double)$this->procurement->paid,
            'amount_to_be_paid' => $this->paymentRequest ? (double)$this->paymentRequest->amount : (double)$this->procurement->due,
            'due_after_amount_to_be_paid' => $this->paymentRequest ?
                (double)$this->procurement->totalPrice - ($this->procurement->paid + $this->paymentRequest->amount) : (double)$this->procurement->due
        ];

        if ($data['type'] == self::BILL) $data += [
            'code' => $this->procurement->billCode(),
            'paid' => $this->paymentRequest ? (double)$this->paymentRequest->amount : (double)$this->procurement->paid,
            'payment_date' => $this->getPaymentDate(),
            'payment_method' => 'Cash On Delivery'
        ];

        $total = $this->procurement->totalPrice;
        $total_in_words = (new NumberFormatter("en", NumberFormatter::SPELLOUT))->format($total);
        $total_amount_in_words= ucwords(str_replace('-', ' ', $total_in_words));
        $data['total_amount_in_word'] = $total_amount_in_words . ' Only';

        return $data;
    }

    private function getPaymentDate()
    {
        if ($this->paymentRequest) return $this->paymentRequest->statusChangeLogs()->orderBy('id', 'desc')->first()->created_at->format('d M, Y');
        if ($this->procurement->closed_and_paid_at) return $this->procurement->closed_and_paid_at->format('d M, Y');
        return null;
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
                'unit_price' => $unit ? number_format($field->result / $unit, 2) : 0,
                'total_price' => $field->result
            ]);
        }

        return $item_fields;
    }

    private function getType()
    {
        if (!$this->paymentRequest) {
            if ($this->procurement->isClosedAndPaid()) return self::BILL;
            return self::INVOICE;
        }
        if ($this->paymentRequest->status == Status::APPROVED) return self::BILL;
        return self::INVOICE;
    }
}
