<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PosOrderItem extends Model
{
    protected $guarded = ['id'];

    /**
     * @var number
     */
    private $vat;
    /**
     * @var float|int|number
     */
    private $priceWithVat;
    /**
     * @var float|int
     */
    private $discountAmount;
    /**
     * @var float|int|number
     */
    private $total;
    /**
     * @var float|int
     */
    private $price;
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
        $this->vat = ($this->price * $this->vat_percentage) / 100;
        $this->priceWithVat = $this->price + $this->vat;
        $this->discountAmount = ($this->price > $this->discount) ? $this->discount : $this->price;
        $this->total = $this->priceWithVat - $this->discount;
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
}
