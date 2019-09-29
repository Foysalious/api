<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerMobile extends Model {
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}