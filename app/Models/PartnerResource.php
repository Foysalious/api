<?php namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Sheba\Dal\Category\Category;

class PartnerResource extends Model
{
    use HasFactory;

    protected $table = 'partner_resource';
    protected $guarded = ['id'];

    public function resource()
    {
        return $this->belongsTo(Resource::class);
    }

    public function partners()
    {
        return $this->belongsToMany(Partner::class);
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }

    public function scopeHandyman($query)
    {
        return $query->where('resource_type', 'Handyman');
    }
}
