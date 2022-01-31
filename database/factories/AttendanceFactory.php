<?php
namespace Database\Factories;

use Carbon\Carbon;
use Sheba\Dal\Attendance\Model as Attendance;

class AttendanceFactory extends Factory
{
    protected $model = Attendance::class;

    public function definition(): array
    {
        return array_merge($this->commonSeeds, [
            'date'                      =>Carbon::now(),
            'checkin_time'              =>'09:01:17',
            'checkout_time'             =>'18:01:17',
            'staying_time_in_minutes'   =>'1500.00',
            'status'                    =>'on_time',
            'is_attendance_reconciled'  =>0,
        ]);
    }
}
