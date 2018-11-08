<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerFavorite extends Model
{
    protected $guarded = ['id'];
    protected $table = 'customer_favourites';

    protected $servicePivotColumns = ['name', 'additional_info', 'variable_type', 'variables', 'option', 'quantity'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function customer()
    {
        return $this->belongsTo(CustomerFavorite::class);
    }

    public function job()
    {
        return $this->belongsTo(Job::class);
    }

    public function services()
    {
        return $this->belongsToMany(Service::class, 'customer_favourite_service', 'customer_favourite_id')->withPivot($this->servicePivotColumns);
    }

    public function partner()
    {
        return $this->belongsTo(Partner::class);
    }
}
