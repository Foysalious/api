<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CategoryPartner extends Model
{
    protected $table = 'category_partner';

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function partner()
    {
        return $this->belongsTo(Partner::class);
    }
}
