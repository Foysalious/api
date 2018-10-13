<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TopUpVendor extends Model
{
    protected $guarded = ['id'];
    protected $table = 'topup_vendors';
    protected $casts = ['sheba_commission' => 'double', 'agent_commission' => 'double', 'is_published' => 'int', 'amount' => 'double'];
}