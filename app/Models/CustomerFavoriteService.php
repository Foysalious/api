<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerFavoriteService extends Model
{
    protected $table = ['customer_favourite_service'];

    public function favorite()
    {
        return $this->belongsTo(CustomerFavourite::class);
    }
}