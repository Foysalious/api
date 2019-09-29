<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CategoryGroupCategory extends Model
{
    protected $table = 'category_group_category';
    protected $fillable = ['category_group_id', 'category_id', 'order'];
    public $timestamps = false;
}
