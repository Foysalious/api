<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RewardShopProduct extends Model
{
    protected $guarded = ['id'];
    protected $table = 'reward_products';
    protected $casts = ['point' => 'integer'];

    public function scopePublished($query, $status = true)
    {
        return $query->where('publication_status', $status);
    }
}
