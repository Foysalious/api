<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
	protected $guarded = ['id'];

    public function customers()
    {
        return $this->morphedByMany(Customer::class, 'taggable');
    }

    public function services()
    {
        return $this->morphedByMany(Service::class, 'taggable');
    }

    public function partnerTransactions()
    {
        return $this->morphedByMany(PartnerTransaction::class, 'taggable');
    }

    public function scopeOf($query, $taggable)
    {
        return $query->where('taggable_type', get_class($taggable))->get()->pluck('name', 'id');
    }
}
