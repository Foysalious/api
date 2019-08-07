<?php namespace Sheba\PaymentLink;

use App\Models\Payment;
use Sheba\Reports\PdfHandler;

class InvoiceCreator
{
    /** @var Payment */
    private $payment;
    /** @var PaymentLinkTransformer */
    private $paymentLinkTransFormer;

    public function setPayment(Payment $payment)
    {
        $this->payment = $payment;
        return $this;
    }

    public function setPaymentLink(PaymentLinkTransformer $paymentLinkTransFormer)
    {
        $this->paymentLinkTransFormer = $paymentLinkTransFormer;
        return $this;
    }

    /**
     * @return string
     * @throws \Sheba\Reports\Exceptions\NotAssociativeArray
     */
    public function save()
    {
        $pdf_handler = new PdfHandler();
        $user = $this->paymentLinkTransFormer->getPaymentReceiver();
        $pos_order = $this->paymentLinkTransFormer->getTarget();
        if ($pos_order) $pos_order = $pos_order->calculate();
        $info = [
            'amount' => $this->payment->payable->amount,
            'method' => $this->payment->paymentDetails->last()->readable_method,
            'description' => $this->payment->payable->description,
            'created_at' => $this->payment->created_at->format('jS M, Y, h:i A'),
            'payment_receiver' => [
                'name' => $user->name,
                'image' => $user->logo,
                'mobile' => $user->getMobile(),
                'address' => $user->address
            ],
            'payer' => [
                'name' => $this->payment->payable->getName(),
                'mobile' => $this->payment->payable->getMobile()
            ],
            'pos_order' => $pos_order ? [
                'items' => $pos_order->items,
                'discount' => $pos_order->getAppliedDiscount(),
                'total' => $pos_order->getNetBill(),
                'grand_total' => $pos_order->getTotalBill(),
                'paid' => $pos_order->getPaid(), 'due' => $pos_order->getDue(),
                'status' => $pos_order->getPaymentStatus(),
                'vat' => $pos_order->getTotalVat()] : null
        ];
        return $pdf_handler->setData($info)->setName($this->payment->transaction_id)->setViewFile('transaction_invoice')->save();
    }

}