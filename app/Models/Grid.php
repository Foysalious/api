<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Grid extends Model
{
    protected $guarded = ['id'];
    protected $blockPivotColumns = ['location_id', 'order'];

    public function getSettingsName()
    {
        return 'HomeGrid';
    }

    public function blocks()
    {
        return $this->belongsToMany(Block::class)->withPivot($this->blockPivotColumns);
    }
}