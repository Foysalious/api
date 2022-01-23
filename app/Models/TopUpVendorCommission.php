<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TopUpVendorCommission extends Model
{
    use HasFactory;
    
    protected $guarded = ['id'];
    protected $table = 'topup_vendor_commissions';
}
