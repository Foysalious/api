<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseRequest extends Model
{
    protected $guarded = ['id'];
    protected $dates = ['estimated_date'];

    public function formTemplate()
    {
        return $this->belongsTo(FormTemplate::class, 'form_template_id');
    }

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function items()
    {
        return $this->hasMany(PurchaseRequestItem::class);
    }

    public function questions()
    {
        return $this->hasMany(PurchaseRequestQuestion::class);
    }
}