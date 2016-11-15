<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Service extends Model {

    public function scopePublished($query)
    {
        $query->where('publication_status', 1);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function subCategory()
    {
        return $this->category()->with('parent');
    }

}