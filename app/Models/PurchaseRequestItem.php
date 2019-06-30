<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseRequestItem extends Model
{
    protected $guarded = ['id'];

    public function fields()
    {
        return $this->hasMany(PurchaseRequestItemField::class);
    }
}