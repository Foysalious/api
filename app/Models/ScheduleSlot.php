<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DB;

class ScheduleSlot extends Model
{
    use HasFactory;
    
    const SCHEDULE_START = '09:00:00';
    const SCHEDULE_END = '21:00:00';
    protected $guarded = ['id'];

    public function scopeShebaSlots($q)
    {
        return $q->where([
            ['start', '>=', DB::raw("CAST('".self::SCHEDULE_START."' As time)")],
            ['end', '<=', DB::raw("CAST('".self::SCHEDULE_END."' As time)")],
        ]);
    }
}
