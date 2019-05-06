<?php namespace App\Models\Transport;

use Illuminate\Database\Eloquent\Model;
use Sheba\Voucher\Contracts\CanHaveVoucher;

class TransportTicketOrder  extends Model implements CanHaveVoucher
{
    protected $guarded = ['id'];
}
