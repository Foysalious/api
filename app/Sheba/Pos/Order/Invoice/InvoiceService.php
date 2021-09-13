<?php namespace App\Sheba\Pos\Order\Invoice;

use App\Models\PosOrder;
use Sheba\Pos\Repositories\PosOrderRepository;
use Sheba\Reports\Exceptions\NotAssociativeArray;
use Sheba\Reports\PdfHandler;
use Sheba\Subscription\Partner\Access\RulesDescriber\Pos;

class InvoiceService
{
    /**
     * @var PosOrderRepository
     */
    private $posOrderRepository;
    /**
     * @var string
     */
    private $invoiceLink;

    public function __construct(PosOrderRepository $posOrderRepository)
    {
        $this->posOrderRepository = $posOrderRepository;
    }

    /**
     * @var $posOrder PosOrder
     */
    private $posOrder;

    /**
     * @param mixed $posOrder
     * @return InvoiceService
     */
    public function setPosOrder(PosOrder $posOrder)
    {
        $this->posOrder = $posOrder;
        return $this;
    }

    public function isAlreadyGenerated()
    {
        $this->invoiceLink = PosOrder::find($this->posOrder->id)->invoice;
        return $this;
    }

    public function getInvoiceLink()
    {
        return $this->invoiceLink;
    }
    /**
     * @throws NotAssociativeArray
     */
    public function generateInvoice()
    {
        $data = $this->generateData();
        $invoice_name = 'pos_order_invoice_' . $this->posOrder->id;
        $this->invoiceLink         =  (new PdfHandler())->setData($data)->setName($invoice_name)->setViewFile('order_invoice')->save(true);
        return $this;
    }

    public function saveInvoiceLink()
    {
        $this->posOrderRepository->update($this->posOrder,['invoice' => $this->invoiceLink]);
        return $this;
    }

    /**
     * @return array
     */
    private function generateData()
    {
        $pos_order   = $this->posOrder->calculate();
        $partner     = $pos_order->partner;
        $info        = [
            'amount'           => $pos_order->getNetBill(),
            'created_at'       => $pos_order->created_at->format('jS M, Y, h:i A'),
            'payment_receiver' => [
                'name'                    => $partner->name,
                'image'                   => $partner->logo,
                'mobile'                  => $partner->getContactNumber(),
                'address'                 => $partner->address,
                'vat_registration_number' => $partner->vat_registration_number
            ],
            'pos_order'        => $pos_order ? [
                'items'       => $pos_order->items,
                'discount'    => $pos_order->getTotalDiscount(),
                'total'       => $pos_order->getTotalPrice(),
                'grand_total' => $pos_order->getTotalBill(),
                'paid'        => $pos_order->getPaid(),
                'due'         => $pos_order->getDue(),
                'status'      => $pos_order->getPaymentStatus(),
                'vat'         => $pos_order->getTotalVat(),
                'delivery_charge' => $pos_order->delivery_charge
            ] : null
        ];
        if ($pos_order->customer) {
            $customer     = $pos_order->customer->profile;
            $info['user'] = [
                'name'   => $customer->name,
                'mobile' => $customer->mobile,
                'address' => !$pos_order->address? $customer->address: $pos_order->address
            ];
        }
        return $info;
    }





}
