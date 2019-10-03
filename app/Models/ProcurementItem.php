<?php namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class ProcurementItem extends Model
{
    protected $guarded = ['id'];

    public function fields()
    {
        return $this->hasMany(ProcurementItemField::class, 'procurement_item_id');
    }
}