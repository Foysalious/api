<?php namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Sheba\Business\BusinessBasicInformation;
use App\Models\BusinessMember;
use App\Transformers\Employee\ShiftCalenderTransformer;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use League\Fractal\Serializer\ArraySerializer;
use Sheba\Helpers\TimeFrame;
use Illuminate\Http\Request;
use Sheba\Dal\ShiftCalender\ShiftCalenderRepository;

class ShiftCalenderController extends Controller
{
    use BusinessBasicInformation;

    /* @var ShiftCalenderRepository */
    private $shiftCalenderRepository;

    public function __construct()
    {
        $this->shiftCalenderRepository = app(ShiftCalenderRepository::class);
    }

    public function index(Request $request, TimeFrame $time_frame)
    {
        /** @var BusinessMember $business_member */
        $business_member = $this->getBusinessMember($request);
        if (!$business_member) return api_response($request, null, 401);

        $month = $request->month;
        $year = $request->year;
        $time_frame = $time_frame->forAMonth($month, $year);
        $shift_calender = $this->shiftCalenderRepository->builder()->where('business_member_id', $business_member->id)->whereBetween('date', [$time_frame->start, $time_frame->end])->get();
        $manager = new Manager();
        $manager->setSerializer(new ArraySerializer());
        $resource = new Collection($shift_calender, new ShiftCalenderTransformer());
        $shift_calender = $manager->createData($resource)->toArray()['data'];
        dd($shift_calender);

        $shift_calender_employee_data = (new ShiftCalenderTransformer())->transform($shift_calender);
        return api_response($request, $shift_calender, 200, ['shift_calender' => $shift_calender_employee_data]);
    }
}