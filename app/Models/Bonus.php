<?php namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Bonus extends Model
{
    protected $guarded = ['id'];
    protected $casts = ['amount' => 'double'];
    protected $dates = ['valid_till'];

    public function spentOn()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->morphTo();
    }

    public function scopeValid($query)
    {
        return $query->status('valid');
    }

    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeValidationDateOver($query)
    {
        return $query->where('valid_till', '<', Carbon::now()->toDateTimeString());
    }
}