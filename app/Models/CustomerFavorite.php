<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerFavourite extends Model
{
    protected $guarded = ['id'];

    protected $servicePivotColumns = ['name', 'additional_info', 'variable_type', 'variables', 'option', 'quantity'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function services()
    {
        return $this->belongsToMany(CustomerFavoriteService::class)->withPivot($this->servicePivotColumns);
    }
}
