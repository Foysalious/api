<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class PartnerDailyStat extends Model
{
    protected $guarded = ['id'];
    protected $dates = ['date'];
    public $timestamps = false;

    public static function boot()
    {
        parent::boot();
        static::creating( function ($model) {
            $model->setCreatedAt($model->freshTimestamp());
        });
    }

    public function partner()
    {
        return $this->belongsTo(Partner::class);
    }

    public function getDataAttribute($data)
    {
        return json_decode($data);
    }

    public static function insertIgnore(array $array){
        $a = new static();
        $table = $a->getTable();
        if(count($array) == count($array, COUNT_RECURSIVE)) {
            $columns = implode(',', array_keys($array));
            $placeholders = "(?" . str_repeat(', ?', count($array) - 1) . ")";
            $values = array_values($array);
        } else {
            $rows = array_values($array);
            $columns = [];
            $placeholders = [];
            $values = [];
            foreach($rows as $row) {
                $columns += array_keys($row);
                $placeholders[] = "(?" . str_repeat(', ?', count($row) - 1) . ")";
                foreach($row as $cell) $values[] = $cell;
            }
            $columns = implode(',', $columns);
            $placeholders = implode(',', $placeholders);
        }
        DB::insert("INSERT IGNORE INTO $table ($columns) VALUES $placeholders", $values);
    }
}
