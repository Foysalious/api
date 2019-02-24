<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class ServiceSubscription extends Model
{
    protected $guarded = ['id'];

    public function isPercentage()
    {
        return (int)$this->is_discount_amount_percentage;
    }

    public function hasCap()
    {
        return $this->cap > 0;
    }
}