<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PartnerBankLoan extends Model
{
    protected $guarded = ['id'];


    public function partners()
    {
        return $this->hasMany(Partner::class);
    }
}
