<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PosOrderPayment extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
    protected $casts = ['amount' => 'double'];

    public function order()
    {
        return $this->belongsTo(PosOrder::class);
    }

    public function scopeType($query, $type)
    {
        return $query->where('transaction_type', $type);
    }

    public function scopeDebit($query)
    {
        return $query->type('Debit');
    }

    public function scopeCredit($query)
    {
        return $query->type('Credit');
    }
}
