<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PosOrderItem extends Model
{
    protected $guarded = ['id'];
    /**
     * @var number
     */
    private $servicePrice;
    private $serviceDiscounts;
    /**
     * @var int|number
     */
    private $grossPrice;
    /**
     * @var number
     */
    private $unitPrice;
    /**
     * @var bool
     */
    private $isCalculated;

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
