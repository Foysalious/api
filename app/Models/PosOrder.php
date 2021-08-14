<?php namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Sheba\Dal\BaseModel;
use Sheba\Dal\POSOrder\Events\PosOrderSaved as PosOrderSavedEvent;
use Sheba\Dal\POSOrder\OrderStatuses as POSOrderStatuses;
use Sheba\EMI\Calculations;
use Sheba\Helpers\TimeFrame;
use Sheba\PaymentLink\Target;
use Sheba\PaymentLink\TargetType;
use Sheba\Pos\Log\Supported\Types;
use Sheba\Pos\Order\OrderPaymentStatuses;
use Sheba\Pos\Order\RefundNatures\Natures;
use Sheba\Pos\Order\RefundNatures\ReturnNatures;
use Sheba\Dal\POSOrder\SalesChannels;
use Sheba\Dal\POSOrder\OrderStatuses;


class PosOrder  extends BaseModel
{
    use SoftDeletes;

    /** @var bool */
    public $isCalculated;
    protected $guarded = ['id'];
    protected $dates = ['deleted_at'];
    /** @var string */
    private $paymentStatus;
    /** @var float */
    private $paid;
    /** @var float */
    private $due;
    /**@var float|int */
    private $totalPrice;
    /** @var number */
    private $totalVat;
    /** @var float|int */
    private $totalItemDiscount;
    /** @var float|int|number */
    private $totalBill;
    /** @var float|int */
    private $totalDiscount;
    /** @var float|int|number */
    private $appliedDiscount;
    /** @var float|int|number */
    private $netBill;
    private $originalTotal;

    public static $savedEventClass = PosOrderSavedEvent::class;

    public function calculate()
    {
        $this->_calculateThisItems();
        $this->totalDiscount = $this->totalItemDiscount + $this->discountsAmountWithoutService();
        $this->appliedDiscount = ($this->discountsAmountWithoutService() > $this->totalBill) ? $this->totalBill : $this->discountsAmountWithoutService();
        $this->originalTotal = round($this->totalBill - $this->appliedDiscount, 2);
        if (isset($this->emi_month) && !$this->interest) {
            $data = Calculations::getMonthData($this->originalTotal, (int)$this->emi_month, false);
            $this->interest = $data['total_interest'];
            $this->bank_transaction_charge = $data['bank_transaction_fee'];
            $this->update(['interest' => $this->interest, 'bank_transaction_charge' => $this->bank_transaction_charge]);
        }
        $this->netBill = $this->originalTotal + round((double)$this->interest, 2) + (double)round($this->bank_transaction_charge, 2);
        if ($this->delivery_charge && !in_array($this->status, [POSOrderStatuses::CANCELLED, POSOrderStatuses::DECLINED])) $this->netBill += (double)round($this->delivery_charge, 2);
        $this->_calculatePaidAmount();
        $this->paid = round($this->paid ?: 0, 2);

        $this->due = ($this->netBill - $this->paid) > 0 ? ($this->netBill - $this->paid) : 0;
        $this->_setPaymentStatus();
        $this->isCalculated = true;
        $this->_formatAllToTaka();
        return $this;
    }

    private function _calculateThisItems()
    {
        $this->_initializeTotalsToZero();
        foreach ($this->items as $item) {
            /** @var PosOrderItem $item */
            $item = $item->calculate();
            $this->_updateTotalPriceAndCost($item);
        }
        return $this;
    }

    private function _initializeTotalsToZero()
    {
        $this->totalPrice = 0;
        $this->totalVat = 0;
        $this->totalItemDiscount = 0;
        $this->totalBill = 0;
    }

    private function _updateTotalPriceAndCost(PosOrderItem $item)
    {
        $this->totalPrice += $item->getPrice();
        $this->totalVat += $item->getVat();
        $this->totalItemDiscount += $item->getDiscountAmount();
        $this->totalBill += $item->getTotal();
    }

    public function discountsAmountWithoutService()
    {
        return $this->discountsWithoutService()->sum('amount');
    }

    public function discountsWithoutService()
    {
        return $this->discounts()->whereNull('item_id');
    }

    public function discounts()
    {
        return $this->hasMany(PosOrderDiscount::class);
    }

    public function log()
    {
        return $this->has(PosOrderDiscount::class);
    }

    private function _calculatePaidAmount()
    {
        /**
         * USING AS A QUERY, THAT INCREASING LOAD TIME ON LIST VIEW
         *
         * $credit = $this->creditPayments()->sum('amount');
         * $debit  = $this->debitPayments()->sum('amount');
         *
         */
        $credit = $this->creditPaymentsCollect()->sum('amount');
        $debit = $this->debitPaymentsCollect()->sum('amount');
        $this->paid = $credit - $debit;
    }

    private function creditPaymentsCollect()
    {
        return $this->payments->filter(function ($payment) {
            return $payment->transaction_type === 'Credit';
        });
    }

    private function debitPaymentsCollect()
    {
        return $this->payments->filter(function ($payment) {
            return $payment->transaction_type === 'Debit';
        });
    }

    private function _setPaymentStatus()
    {
        $this->paymentStatus = ($this->due) ? OrderPaymentStatuses::DUE : OrderPaymentStatuses::PAID;
        return $this;
    }

    private function _formatAllToTaka()
    {
        $this->totalPrice = formatTakaToDecimal($this->totalPrice);
        $this->totalVat = formatTakaToDecimal($this->totalVat);
        $this->totalItemDiscount = formatTakaToDecimal($this->totalItemDiscount);
        $this->totalBill = formatTakaToDecimal($this->totalBill);
        return $this;
    }

    public function customer()
    {
        return $this->belongsTo(PosCustomer::class);
    }

    public function partner()
    {
        return $this->belongsTo(Partner::class);
    }

    public function items()
    {
        return $this->hasMany(PosOrderItem::class);
    }

    public function refundLogs()
    {
        return $this->logs()->whereIn('type', [
            ReturnNatures::PARTIAL_RETURN,
            ReturnNatures::FULL_RETURN
        ]);
    }

    public function logs()
    {
        return $this->hasMany(PosOrderLog::class);
    }

    public function scopeGetPartnerWiseOrderId($query, $id)
    {
        $pos_order = $query->withTrashed()->where('id', $id)->first();
        return $pos_order ? $pos_order->partner_wise_order_id : null;
    }

    public function scopeByPartner($query, $partner_id)
    {
        return $query->where('partner_id', $partner_id);
    }

    public function scopeByCustomer($query, $customer_id)
    {
        return $query->where('customer_id', $customer_id);
    }

    public function scopeByPartnerAndCustomer($query, $partner_id, $customer_id)
    {
        return $query->where('partner_id', $partner_id)->where('customer_id', $customer_id);
    }

    public function scopeByVoucher($query, $voucher_id)
    {
        if (is_array($voucher_id))
            return $query->whereIn('voucher_id', $voucher_id); else
            return $query->where('voucher_id', $voucher_id);
    }

    public function getRefundAmount()
    {
        return !$this->debitPayments()->get()->isEmpty() ? (double)$this->debitPayments()->sum('amount') : 0.00;
    }

    private function debitPayments()
    {
        return $this->payments()->debit();
    }

    /**
     * @return string
     */
    public function getPaymentStatus()
    {
        return $this->paymentStatus;
    }

    /**
     * @return float
     */
    public function getPaid()
    {
        return $this->paid;
    }

    /**
     * @return float
     */
    public function getDue()
    {
        return $this->due;
    }

    /**
     * @return float|int
     */
    public function getTotalPrice()
    {
        return $this->totalPrice;
    }

    /**
     * @return number
     */
    public function getTotalVat()
    {
        return $this->totalVat;
    }

    /**
     * @return float|int
     */
    public function getTotalItemDiscount()
    {
        return $this->totalItemDiscount;
    }

    /**
     * @return float|int|number
     */
    public function getTotalBill()
    {
        return $this->totalBill;
    }

    /**
     * @return float|int
     */
    public function getTotalDiscount()
    {
        return $this->totalDiscount;
    }

    /**
     * @return float|int|number
     */
    public function getAppliedDiscount()
    {
        return $this->appliedDiscount;
    }

    /**
     * @return float|int|number
     */
    public function getNetBill()
    {
        return $this->netBill;
    }

    public function getRefundStatus()
    {
        /**
         * USING AS A QUERY, THAT INCREASING LOAD TIME ON LIST VIEW
         *
         * $is_exchanged = $this->logs()->refundOf(Types::EXCHANGE)->first();
         * $is_full_returned  = $this->logs()->refundOf(Types::FULL_RETURN)->first();
         * $is_partial_return = $this->logs()->refundOf(Types::PARTIAL_RETURN)->first();
         *
         */
        $is_exchanged = $is_full_returned = $is_partial_return = null;
        $this->logs->each(function ($log) use (&$is_exchanged, &$is_full_returned, &$is_partial_return) {
            $is_exchanged = ($log->type == Types::EXCHANGE) ? $log : null;
            $is_full_returned = ($log->type == Types::FULL_RETURN) ? $log : null;
            $is_partial_return = ($log->type == Types::PARTIAL_RETURN) ? $log : null;
        });
        return $is_exchanged ? Natures::EXCHANGED : (($is_full_returned || $is_partial_return) ? Natures::RETURNED : null);
    }

    public function isRefundable()
    {
        return !$this->previous_order_id;
    }

    public function scopeCreatedAt($query, Carbon $date)
    {
        $query->whereDate('created_at', '=', $date->toDateString());
    }

    public function scopeCreatedAtBetween($query, TimeFrame $time_frame)
    {
        $query->whereBetween('created_at', $time_frame->getArray());
    }

    public function scopeOf($query, $partner)
    {
        if (is_array($partner))
            $query->whereIn('partner_id', $partner); else $query->where('partner_id', '=', $partner);
    }

    public function scopeOfCustomer($query, $customer)
    {
        if (is_array($customer))
            $query->whereIn('customer_id', $customer); else $query->where('customer_id', '=', $customer);
    }

    public function previousOrder()
    {
        return $this->belongsTo(PosOrder::class, 'previous_order_id', 'id');
    }

    private function creditPayments()
    {
        return $this->payments()->credit();
    }

    public function payments()
    {
        return $this->hasMany(PosOrderPayment::class);
    }

    public function scopeWebstoreOrders($query)
    {
        return $query->where('sales_channel', SalesChannels::WEBSTORE);
    }

    public function scopePending($query)
    {
        return $query->where('status', OrderStatuses::PENDING);
    }

    public function scopeProcessing($query)
    {
        return $query->where('status', OrderStatuses::PROCESSING);
    }

    public function scopeShipped($query)
    {
        return $query->where('status', OrderStatuses::SHIPPED);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', OrderStatuses::COMPLETED);
    }

    public function scopeDeclined($query)
    {
        return $query->where('status', OrderStatuses::DECLINED);
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', OrderStatuses::CANCELLED);
    }

    public function scopeSalesChannel($query, $salesChannel)
    {
        return $query->where('sales_channel', $salesChannel);
    }

    public function getPaymentLinkTarget()
    {
        return new Target(TargetType::POS_ORDER, $this->id);
    }
}
