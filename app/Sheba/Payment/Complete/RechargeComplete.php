<?php namespace Sheba\Payment\Complete;

use App\Models\Partner;
use App\Models\Payment;
use Carbon\Carbon;
use DB;
use Illuminate\Database\QueryException;
use ReflectionException;
use Sheba\AccountingEntry\Accounts\Accounts;
use Sheba\AccountingEntry\Exceptions\AccountingEntryServerError;
use Sheba\AccountingEntry\Exceptions\InvalidSourceException;
use Sheba\AccountingEntry\Exceptions\KeyNotFoundException;
use Sheba\AccountingEntry\Repository\JournalCreateRepository;
use Illuminate\Support\Facades\Log;
use Sheba\Dal\PaymentGateway\Contract as PaymentGatewayRepo;
use Sheba\PushNotificationHandler;
use Sheba\Reward\ActionRewardDispatcher;
use Sheba\Transactions\Types;
use Sheba\Transactions\Wallet\HasWalletTransaction;
use Sheba\Transactions\Wallet\WalletTransactionHandler;

class RechargeComplete extends PaymentComplete
{
    private $fee;

    private $transaction;
    private $paymentGateway;

    public function complete()
    {
        try {
            $this->payment->reload();
            if ($this->payment->isComplete()) return $this->payment;
            $this->paymentRepository->setPayment($this->payment);
            DB::transaction(function () {
                $this->storeTransaction();
                $this->completePayment();
                $payable      = $this->payment->payable;
                $payable_user = $payable->user;
                $this->storeCommissionTransaction();
                $this->setPaymentGateWay();
                if ($payable_user instanceof Partner) {
                    $this->notifyManager($this->payment, $payable_user);
                    app(ActionRewardDispatcher::class)->run('partner_wallet_recharge', $payable_user, $payable_user, $payable);
                    $this->storeJournal();
                }
            });
        } catch (QueryException $e) {
            $this->failPayment();
            throw $e;
        }
        return $this->payment;
    }

    private function storeTransaction()
    {
        /** @var HasWalletTransaction $user */
        $user              = $this->payment->payable->user;
        $this->transaction = (new WalletTransactionHandler())->setModel($user)->setAmount((double)$this->payment->payable->amount)->setType(Types::credit())->setLog('Credit Purchase')->setTransactionDetails($this->payment->getShebaTransaction()->toArray())->setSource($this->payment->paymentDetails->last()->method)->store();
    }

    protected function saveInvoice()
    {
        // TODO: Implement saveInvoice() method.
    }

    /**
     * @param $charge
     * @return float
     */
    private function calculateCommission($charge): float
    {
        if ($this->payment->payable->user instanceof Partner) return round(($this->payment->payable->amount / (100 + $charge)) * $charge, 2);
        return (double)round(($charge * $this->payment->payable->amount) / 100, 2);
    }

    private function storeCommissionTransaction()
    {
        /** @var HasWalletTransaction $user */
        $user = $this->payment->payable->user;

        $payment_gateways = app(PaymentGatewayRepo::class);
        $payment_gateway  = $payment_gateways->builder()
            ->where('service_type', $this->payment->created_by_type)
            ->where('method_name', $this->payment->paymentDetails->last()->method)
            ->where('status', 'Published')
            ->first();

        if ($payment_gateway && $payment_gateway->cash_in_charge > 0) {
            $this->fee = $amount = $this->calculateCommission($payment_gateway->cash_in_charge);
            (new WalletTransactionHandler())->setModel($user)
                ->setAmount($amount)
                ->setType(Types::debit())
                ->setLog($amount . ' BDT has been deducted as a gateway charge for SHEBA credit recharge')
                ->setTransactionDetails($this->payment->getShebaTransaction()->toArray())
                ->setSource($this->payment->paymentDetails->last()->method)
                ->store();
        }
    }

    private function setPaymentGateWay()
    {
        $payment_gateways = app(PaymentGatewayRepo::class);
        $this->paymentGateway = $payment_gateways->builder()
            ->where('service_type', $this->payment->created_by_type)
            ->where('method_name', $this->payment->paymentDetails->last()->method)
            ->where('status', 'Published')
            ->first();
    }

    /**
     * @throws ReflectionException
     * @throws AccountingEntryServerError
     * @throws InvalidSourceException|KeyNotFoundException
     */
    private function storeJournal()
    {
        $payable = $this->payment->payable;
        $commission = isset($this->paymentGateway) ? $this->calculateCommission($this->paymentGateway->cash_in_charge) : 0;
        (new JournalCreateRepository())->setTypeId($payable->user->id)
            ->setSource($this->transaction)->setAmount($payable->amount)
            ->setDebitAccountKey((new Accounts())->asset->sheba::SHEBA_ACCOUNT)
            ->setCreditAccountKey($this->payment->paymentDetails->last()->method)
            ->setDetails("Entry For Wallet Transaction")
            ->setCommission($commission)->setEndPoint("api/journals/wallet")
            ->setReference($this->payment->id)->store();

    }

    private function notifyManager(Payment $payment, $partner)
    {
        try {
            $topic = config('sheba.push_notification_topic_name.manager') . $partner->id;
            $channel = config('sheba.push_notification_channel_name.manager');
            $sound = config('sheba.push_notification_sound.manager');
            $formatted_amount = number_format($payment->payable->amount, 2);
            $fee = number_format($this->fee, 2);
            $real_amount = number_format(($payment->payable->amount - $this->fee), 2);
            $payment_completion_date = Carbon::parse($this->payment->updated_at)->format('d/m/Y');
            $message = "{$formatted_amount} টাকা রিচারজ হয়েছে; ফি {$fee} টাকা; আপনি পাবেন {$real_amount} টাকা। at {$payment_completion_date}. sManager (SPL Ltd.)";
            (new PushNotificationHandler())->send([
                "title" => 'Order Successful',
                "message" => $message,
                "event_type" => "wallet_recharge",
                "event_id" => $payment->id,
                "sound" => "notification_sound",
                "channel_id" => $channel
            ], $topic, $channel, $sound);

            notify()->partner($partner)->send([
                "title" => "ক্রেডিট রিচার্জ ৳" . en2bnNumber($formatted_amount),
                "description" => $message,
                "type" => "Info",
                "event_type" => "wallet_recharge"
            ]);
        } catch (\Throwable $exception) {
            Log::info($exception);
        }
    }
}
