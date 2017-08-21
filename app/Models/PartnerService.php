<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class PartnerService extends Model
{
    protected $table = 'partner_service';

    public function locations()
    {
        return $this->belongsToMany(Location::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function partner()
    {
        return $this->belongsTo(Partner::class);
    }

    public function commission()
    {
        return $this->partner->categories()->find($this->service->category->id)->pivot->commission;
    }

    public function discounts()
    {
        return $this->hasMany(PartnerServiceDiscount::class);
    }

    public function runningDiscounts()
    {
        $now = Carbon::now();
        return $this->discounts()->where(function ($query) use ($now) {
            $query->where('start_date', '<=', $now);
            $query->where('end_date', '>=', $now);
        })->get();
    }

    public function discount()
    {
        return $this->runningDiscounts()->first();
    }

    public function scopePublished($query)
    {
        return $query->where([
            ['is_published', 1],
            ['is_verified', 1]
        ]);
    }
}
