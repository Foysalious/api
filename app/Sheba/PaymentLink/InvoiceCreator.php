<?php namespace Sheba\PaymentLink;

use App\Models\Payment;
use Sheba\Reports\Exceptions\NotAssociativeArray;
use Sheba\Reports\PdfHandler;

class InvoiceCreator
{
    /** @var Payment */
    private $payment;
    /** @var PaymentLinkTransformer */
    private $paymentLinkTransFormer;

    /**
     * @param Payment $payment
     * @return $this
     */
    public function setPayment(Payment $payment)
    {
        $this->payment = $payment;
        return $this;
    }

    /**
     * @param PaymentLinkTransformer $paymentLinkTransFormer
     * @return $this
     */
    public function setPaymentLink(PaymentLinkTransformer $paymentLinkTransFormer)
    {
        $this->paymentLinkTransFormer = $paymentLinkTransFormer;
        return $this;
    }

    /**
     * @return string
     * @throws NotAssociativeArray
     */
    public function save()
    {
        $pdf_handler = new PdfHandler();
        $user = $this->paymentLinkTransFormer->getPaymentReceiver();
        $pos_order = $this->paymentLinkTransFormer->getTarget();
        if ($pos_order) $pos_order = $pos_order->calculate();
        $info = [
            'payment_id' => $this->payment->id,
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
            'user' => [
                'name' => $this->payment->payable->getName(),
                'mobile' => $this->payment->payable->getMobile()
            ],
            'pos_order' => $pos_order ? [
                'items' => $pos_order->items,
                'discount' => $pos_order->getTotalDiscount(),
                'total' => $pos_order->getTotalPrice(),
                'grand_total' => $pos_order->getTotalBill(),
                'paid' => $pos_order->getPaid(), 'due' => $pos_order->getDue(),
                'status' => $pos_order->getPaymentStatus(),
                'vat' => $pos_order->getTotalVat()] : null
        ];

        return $pdf_handler->setData($info)->setName($this->payment->transaction_id)->setViewFile('transaction_invoice')->save();
    }
}
