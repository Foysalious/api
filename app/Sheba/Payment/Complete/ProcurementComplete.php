<?php namespace Sheba\Payment\Complete;

use App\Models\Procurement;
use Illuminate\Database\QueryException;
use Sheba\Business\ProcurementPayment\Creator;
use DB;
use Sheba\FraudDetection\TransactionSources;
use Sheba\Transactions\Wallet\WalletTransactionHandler;

class ProcurementComplete extends PaymentComplete
{
    public function complete()
    {
        try {
            if ($this->payment->isComplete()) return $this->payment;
            $this->paymentRepository->setPayment($this->payment);
            $payable = $this->payment->payable;
            $this->setModifier($payable->user);
            /** @var Procurement $procurement */
            $procurement = $payable->getPayableType();
            DB::transaction(function () use ($procurement, $payable) {
                /** @var Creator $creator */
                $creator = app(Creator::class);
                foreach ($this->payment->paymentDetails as $payment_detail) {
                    if ($payment_detail->amount == 0) continue;
                    $creator->setProcurement($procurement)->setAmount($payable->amount)->setPaymentMethod($payment_detail->readable_method)->setPaymentType('Debit');
                    $creator->create();
                    $this->updateShebaCollection($procurement);
                }
            });
            $this->payment->transaction_details = null;
            $this->completePayment();

            $partner = $procurement->getActiveBid()->bidder;
            $procurement->calculate();
            if ($procurement->status == 'served' && $procurement->due == 0) {
                $price = $procurement->totalPrice;
                $price_after_commission = $price - (($price * $partner->commission) / 100);
                if ($price_after_commission > 0) app(WalletTransactionHandler::class)->setModel($partner)->setAmount($price_after_commission)->setSource(TransactionSources::SERVICE_PURCHASE)->setType('credit')->setLog("Credited for RFQ ID:" . $procurement->id)->dispatch();
            }
        } catch (QueryException $e) {
            $this->failPayment();
            throw $e;
        }
        $this->completePayment();
        return $this->payment;
    }

    private function updateShebaCollection(Procurement $procurement)
    {
        $procurement->sheba_collection += $this->payment->payable->amount;
        $procurement->update();
    }

    protected function saveInvoice()
    {
        // TODO: Implement saveInvoice() method.
    }
}
