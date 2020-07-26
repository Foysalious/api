<?php namespace App\Models;

use Sheba\Dal\PartnerBankLoan\Statuses as LoanStatuses;
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

    public function scopeType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeTypeAndStatus($query, $type, $status)
    {
        return $query->where('type', $type)->where('status',$status);
    }

    public function rejectedLog()
    {
        $this->changeLogs()->where('title','status')->where('to', LoanStatuses::DECLINED)->orWhere('to', LoanStatuses::REJECTED)->get()->last();
    }

}
