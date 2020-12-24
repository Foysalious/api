<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PartnerBankLoanChangeLog extends model
{
    protected $guarded = ['id'];
    public function setUpdatedAtAttribute($value)
    {
        // to Disable updated_at
    }

}
