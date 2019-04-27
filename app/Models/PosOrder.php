<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Sheba\Pos\Log\Supported\Types;
use Sheba\Pos\Order\OrderPaymentStatuses;
use Sheba\Pos\Order\RefundNatures\Natures;

class PosOrder extends Model
{
    protected $guarded = ['id'];
    protected $casts = ['discount' => 'double', 'discount_percentage' => 'double'];

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
        $this->totalDiscount = $this->totalItemDiscount + $this->discount;
        $this->appliedDiscount = ($this->discount > $this->totalBill) ? $this->totalBill : $this->discount;
        $this->netBill = $this->totalBill - $this->appliedDiscount;
        $this->_calculatePaidAmount();
        $this->paid = $this->paid ?: 0;
        $this->due = $this->netBill - $this->paid;
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
        $credit = $this->creditPayments()->sum('amount');
        $debit  = $this->debitPayments()->sum('amount');

        $this->paid = $credit - $debit;
    }

    public function logs()
    {
        return $this->hasMany(PosOrderLog::class);
    }

    public function scopeByPartner($query, $partner_id)
    {
        return $query->where('partner_id', $partner_id);
    }

    private function creditPayments()
    {
        return $this->payments()->credit();
    }

    private function debitPayments()
    {
        return $this->payments()->debit();
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
        $is_exchanged = $this->logs()->refundOf(Types::EXCHANGE)->first();
        $is_full_returned  = $this->logs()->refundOf(Types::FULL_RETURN)->first();
        $is_partial_return = $this->logs()->refundOf(Types::PARTIAL_RETURN)->first();

        return $is_exchanged ? Natures::EXCHANGED : (($is_full_returned || $is_partial_return) ? Natures::RETURNED : null);
    }
}
