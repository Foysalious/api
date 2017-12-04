<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PartnerResource extends Model
{
    protected $table = 'partner_resource';
    protected $guarded = [
        'id'
    ];

    public function details()
    {
        return $this->belongsToMany(Resource::class);
    }

    public function partners()
    {
        return $this->belongsToMany(Partner::class);
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }
}
