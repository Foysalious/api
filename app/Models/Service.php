<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model {
    protected $fillable = [
        'category_id',
        'name',
        'description',
        'publication_status',
        'recurring_possibility',
        'thumb',
        'banner',
        'faqs',
        'variable_type',
        'variables',
        'created_by',
        'created_by_name',
        'updated_by',
        'updated_by_name',
        'created_at',
        'updated_at'
    ];

    protected $servicePivotColumns = ['id', 'description', 'options', 'prices', 'is_published', 'discount', 'discount_start_date', 'discount_start_date', 'is_verified', 'verification_note', 'created_by', 'created_by_name', 'created_at', 'updated_by', 'updated_by_name', 'updated_at'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function subCategory()
    {
        return $this->category()->with('parent');
    }

    public function partners()
    {
        return $this->belongsToMany(Partner::class);
    }
}
