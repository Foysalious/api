<?php namespace App\Http\Controllers\B2b;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Sheba\Business\Attendance\DailyStat as AttendanceDailyStat;
use Sheba\Dal\Attendance\Statuses;

class AttendanceController extends Controller
{

    public function getDailyStats($business, Request $request, AttendanceDailyStat $stat)
    {
        $this->validate($request, [
            'status' => 'string|in:' . implode(',', Statuses::get()),
            'business_department_id' => 'numeric',
            'date' => 'date|date_format:Y-m-d',
        ]);
        $date = $request->has('date') ? Carbon::parse($request->date) : Carbon::now();
        $attendances = $stat->setBusiness($request->business)->setDate($date)->setBusinessDepartment($request->business_department_id)->setStatus($request->status)->get();
        if (count($attendances) == 0) return api_response($request, null, 404);
        return api_response($request, null, 200, ['attendances' => $attendances]);
    }
}