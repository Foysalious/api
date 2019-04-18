<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Sheba\Pos\Order\OrderPaymentStatuses;

class PosOrder extends Model
{
    protected $guarded = ['id'];
    /**
     * @var int
     */
    public $totalServicePrice;
    public $itemDiscounts;
    /**
     * @var int|number
     */
    public $totalPrice;
    public $totalDiscount;
    public $appliedDiscount;
    /**
     * @var float
     */
    public $grossAmount;
    /**
     * @var string
     */
    public $paymentStatus;
    /**
     * @var float
     */
    public $paid;
    /**
     * @var float
     */
    public $due;
    /**
     * @var bool
     */
    public $isCalculated;

    public function calculate()
    {
        $this->_calculateThisItems();
        $this->totalDiscount = $this->itemDiscounts + $this->discount;
        $this->appliedDiscount = (double)($this->totalDiscount > $this->amount) ? $this->amount : $this->discount;
        $this->grossAmount = floatValFormat($this->totalPrice - $this->appliedDiscount);
        $this->_calculatePaidAmount();
        $this->paid = $this->paid ?: 0;
        $this->due = $this->grossAmount - $this->paid;
        $this->_setPaymentStatus();
        $this->isCalculated = true;

        return $this->_formatAllToTaka();
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
        $this->totalServicePrice = 0;
    }

    private function _updateTotalPriceAndCost(PosOrderItem $item)
    {
        $this->totalServicePrice += $item->servicePrice;
        $this->totalPrice += $item->grossPrice;
        $this->itemDiscounts += $item->discount;
    }

    private function _formatAllToTaka()
    {
        $this->totalDiscount = formatTaka($this->totalDiscount);
        $this->appliedDiscount = formatTaka($this->appliedDiscount);
        $this->grossAmount = formatTaka($this->grossAmount);
        $this->totalServicePrice = formatTaka($this->totalServicePrice);
        $this->totalPrice = formatTaka($this->totalPrice);
        $this->itemDiscounts = formatTaka($this->itemDiscounts);

        return $this;
    }

    private function _calculatePaidAmount()
    {
        $this->paid = 0;
        foreach ($this->payments as $payment) {
            $this->paid += $payment->amount;
        }
    }
}
