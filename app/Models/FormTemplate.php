<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FormTemplate extends Model
{
    protected $guarded = ['id',];
    protected $table = 'form_templates';

    public function scopePublished($query)
    {
        return $query->where('is_published', 1);
    }

    public function inspections()
    {
        return $this->hasMany(Inspection::class);
    }
}