<?php namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Sheba\Helpers\TimeFrame;
use Sheba\Pos\Log\Supported\Types;
use Sheba\Pos\Order\OrderPaymentStatuses;
use Sheba\Pos\Order\RefundNatures\Natures;
use Sheba\Pos\Order\RefundNatures\ReturnNatures;

class PosOrder extends Model
{
    protected $guarded = ['id'];

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
    /** @var bool */
    public $isCalculated;

    public function calculate()
    {
        $this->_calculateThisItems();
        $this->totalDiscount = $this->totalItemDiscount + $this->discountsAmountWithoutService();
        $this->appliedDiscount = ($this->discountsAmountWithoutService() > $this->totalBill) ? $this->totalBill : $this->discountsAmountWithoutService();
        $this->netBill = $this->totalBill - $this->appliedDiscount;
        $this->_calculatePaidAmount();
        $this->paid = $this->paid ?: 0;
        $this->due = ($this->netBill - $this->paid) > 0 ? ($this->netBill - $this->paid) : 0;
        $this->_setPaymentStatus();
        $this->isCalculated = true;
        $this->_formatAllToTaka();

        return $this;
    }

    private function _setPaymentStatus()
    {
        $this->paymentStatus = ($this->due) ? OrderPaymentStatuses::DUE : OrderPaymentStatuses::PAID;
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

    public function discounts()
    {
        return $this->hasMany(PosOrderDiscount::class);
    }

    public function discountsWithoutService()
    {
        return $this->discounts()->whereNull('item_id');
    }

    public function discountsAmountWithoutService()
    {
        return $this->discountsWithoutService()->sum('amount');
    }

    public function payments()
    {
        return $this->hasMany(PosOrderPayment::class);
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

    private function _formatAllToTaka()
    {
        $this->totalPrice = formatTakaToDecimal($this->totalPrice);
        $this->totalVat = formatTakaToDecimal($this->totalVat);
        $this->totalItemDiscount = formatTakaToDecimal($this->totalItemDiscount);
        $this->totalBill = formatTakaToDecimal($this->totalBill);

        return $this;
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

    public function logs()
    {
        return $this->hasMany(PosOrderLog::class);
    }

    public function refundLogs()
    {
        return $this->logs()->whereIn('type', [ReturnNatures::PARTIAL_RETURN, ReturnNatures::FULL_RETURN]);
    }

    public function scopeByPartner($query, $partner_id)
    {
        return $query->where('partner_id', $partner_id);
    }

    public function scopeByCustomer($query, $customer_id)
    {
        return $query->where('customer_id', $customer_id);
    }

    public function scopeByVoucher($query, $voucher_id)
    {
        if (is_array($voucher_id))
            return $query->whereIn('voucher_id', $voucher_id);
        else
            return $query->where('voucher_id', $voucher_id);
    }

    private function creditPayments()
    {
        return $this->payments()->credit();
    }

    private function debitPayments()
    {
        return $this->payments()->debit();
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

    public function getRefundAmount()
    {
        return !$this->debitPayments()->get()->isEmpty() ? (double)$this->debitPayments()->sum('amount') : 0.00;
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
        if (is_array($partner)) $query->whereIn('partner_id', $partner);
        else $query->where('partner_id', '=', $partner);
    }

    public function scopeOfCustomer($query, $customer)
    {
        if (is_array($customer)) $query->whereIn('customer_id', $customer);
        else $query->where('customer_id', '=', $customer);
    }

    public function previousOrder()
    {
        return $this->belongsTo(PosOrder::class, 'previous_order_id', 'id');
    }
}
