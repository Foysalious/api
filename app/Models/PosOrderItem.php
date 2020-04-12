<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PosOrderItem extends Model
{
    protected $guarded = ['id'];
    protected $casts = ['vat_percentage' => 'double', 'quantity' => 'double'];

    /** @var number */
    private $vat;
    /** @var float|int|number */
    private $priceWithVat;
    /** @var float|int */
    private $discountAmount;
    /** @var float|int|number */
    private $total;
    /** @var float|int */
    private $price;
    /** @var float|int */
    private $priceAfterDiscount;
    /** @var bool */
    public $isCalculated;

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function service()
    {
        return $this->belongsTo(PartnerPosService::class);
    }

    public function calculate()
    {
        $this->price = ($this->unit_price * $this->quantity);
        $this->discountAmount = $this->discount ? (($this->price > $this->discount->amount) ? $this->discount->amount : $this->price) : 0.00;
        $this->priceAfterDiscount = $this->price - $this->discountAmount;
        $this->vat = ($this->priceAfterDiscount * $this->vat_percentage) / 100;
        $this->priceWithVat = $this->price + $this->vat;
        $this->total = $this->priceWithVat - $this->discountAmount;
        $this->isCalculated = true;
        $this->_formatAllToTaka();

        return $this;
    }

    private function _formatAllToTaka()
    {
        $this->price = formatTakaToDecimal($this->price);
        $this->vat = formatTakaToDecimal($this->vat);
        $this->priceWithVat = formatTakaToDecimal($this->priceWithVat);
        $this->discountAmount = formatTakaToDecimal($this->discountAmount);
        $this->total = formatTakaToDecimal($this->total);
    }

    /**
     * @return float|int
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @return number
     */
    public function getVat()
    {
        return $this->vat;
    }

    /**
     * @return float|int|number
     */
    public function getPriceWithVat()
    {
        return $this->priceWithVat;
    }

    /**
     * @return float|int
     */
    public function getDiscountAmount()
    {
        return $this->discountAmount;
    }

    /**
     * @return float|int|number
     */
    public function getTotal()
    {
        return $this->total;
    }

    public function discount()
    {
        return $this->hasOne(PosOrderDiscount::class, 'item_id');
    }
}
