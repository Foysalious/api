<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PosOrderItem extends Model
{
    protected $guarded = ['id'];
    /**
     * @var number
     */
    public $servicePrice;
    public $serviceDiscounts;
    /**
     * @var int|number
     */
    public $grossPrice;
    /**
     * @var number
     */
    public $unitPrice;
    /**
     * @var bool
     */
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
        $this->servicePrice = formatTaka(($this->unit_price * $this->quantity));
        $this->grossPrice = ($this->servicePrice > $this->discount) ? formatTaka($this->servicePrice - $this->discount) : 0;
        $this->unitPrice = formatTaka($this->unit_price);
        $this->isCalculated = true;

        return $this;
    }
}
