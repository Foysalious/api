<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GridPortal extends Model
{
    protected $table = 'grid_portal';
    public $timestamps = false;

    protected $guarded = ['id'];

    public function grid()
    {
        return $this->belongsTo(Grid::class);
    }
}
