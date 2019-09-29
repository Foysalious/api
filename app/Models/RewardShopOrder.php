<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RewardShopOrder extends Model
{
    protected $guarded = ['id'];
    protected $table = 'reward_orders';

    public function scopeCreator($query, $creator)
    {
        return $query->where('order_creator_type', get_class($creator))
                ->where('order_creator_id', $creator->id);
    }

    public function product()
    {
        return $this->hasOne(RewardShopProduct::class, 'id', 'reward_product_id');
    }
}
