<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PartnerBankLoan extends Model
{
    protected $guarded = ['id'];


    public function partner()
    {
        return $this->belongsTo(Partner::class);
    }

    public function changeLogs()
    {
        return $this->hasMany(PartnerBankLoanChangeLog::class, 'loan_id');
    }

    public function bank()
    {
        return $this->belongsTo(Bank::class);
    }
    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }
}
