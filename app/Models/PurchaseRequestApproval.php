<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseRequestApproval extends Model
{
    protected $guarded = ['id'];

    public function member()
    {
        return $this->belongsTo(Member::class);
    }
}