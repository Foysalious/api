<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Retailer extends Model
{
    protected $guarded = ['id'];

    public function profile()
    {
        return $this->belongsTo(Profile::class);
    }

    public function partner()
    {
        return $this->belongsTo(Profile::class);
    }

    public function loan()
    {
        return $this->belongsTo(PartnerBankLoan::class);
    }

}