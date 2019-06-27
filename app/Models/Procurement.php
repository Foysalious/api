<?php namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Procurement extends Model
{
    protected $guarded = ['id'];

    public function items()
    {
        return $this->hasMany(ProcurementItem::class);
    }

    public function questions()
    {
        return $this->hasMany(ProcurementQuestion::class);
    }

}