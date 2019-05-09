<?php namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class BusinessTransaction extends Model
{
    protected $guarded = ['id'];
    protected $casts = ['amount' => 'double'];
    public $timestamps = false;

    public function scopeType($query, $type)
    {
        return $query->where('type', $type);
    }
}