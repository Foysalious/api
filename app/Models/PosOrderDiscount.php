<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PosOrderDiscount extends Model
{
    protected $guarded = ['id'];

    public function posOrder()
    {
        return $this->belongsTo(PosOrder::class);
    }

    public function posOrderItem()
    {
        return $this->belongsTo(PosOrderItem::class);
    }

    public function discount()
    {
        return $this->belongsTo(PartnerPosServiceDiscount::class);
    }

    public function getShebaContributionAmount()
    {
        return $this->getContributionAmount('sheba_contribution');
    }

    public function getPartnerContributionAmount()
    {
        return $this->getContributionAmount('partner_contribution');
    }

    private function getContributionAmount($field)
    {
        return ($this->amount * $this->$field) / 100;
    }

    public function scopeByVoucher($query, $order_id)
    {
        return $query->where('type', 'voucher')->whereIn('pos_order_id', $order_id);
    }
}