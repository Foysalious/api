<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Sheba\Voucher\Contracts\CanHaveVoucher;

class MovieTicketOrder extends Model implements CanHaveVoucher
{
    protected $guarded = ['id'];

    public function isFailed()
    {
        return $this->status == 'Failed';
    }

    public function isSuccess()
    {
        return $this->status == 'Success';
    }
}
